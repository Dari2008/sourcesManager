import { useEffect, useRef, useState } from "react";
import "./Main.scss";
import { loadSourceData, parseUrl } from "../../components";
import { saveSourceData, type Source } from "../../components/dataLoader/DataLoader";

import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";
import ExportDialog from "../../components/exports/ExportDialog";
dayjs.extend(customParseFormat);

export default function Main() {
    const [hasToReload, setHasToReload] = useState<boolean>(false);
    const [data, setData] = useState<Source[]>(() => loadSourceData());
    if (hasToReload) {
        setData(loadSourceData());
        setHasToReload(false);
    }
    const exportDialogRef = useRef<HTMLDialogElement>(null);
    const lastPastedRef = useRef<string>("");
    const exportBtnRef = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        window.addEventListener("paste", (e) => {
            const items = e.clipboardData?.items;
            if (!items) return;
            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                if (item.kind !== "string") continue;
                if (item.type !== "text/plain") continue;
                item.getAsString((s) => {
                    if (lastPastedRef.current == s) return;
                    if (!s.startsWith("http")) return;
                    console.log("Pasted URL:", s);
                    lastPastedRef.current = s;
                    setData(pastedURL(s));
                });
            }
        });
    }, []);

    return <div className="main">
        <div className="top">
            <h1>Sources Manager</h1>
            <button className="exportBtn" onClick={() => exportDialogRef.current?.showModal()} ref={exportBtnRef}>Export</button>
        </div>
        <ExportDialog sources={data} ref={exportDialogRef} buttonRef={exportBtnRef}></ExportDialog>
        <table>
            <thead>
                <tr>
                    <th className="url">URL</th>
                    <th className="ttitle">Title</th>
                    <th className="author">Author</th>
                    <th className="pageName">Page Name</th>
                    <th className="dateOfPage">Date of Page</th>
                    <th className="dateLastVisited">Date Last Visited</th>
                    <th className="delete">Actions</th>
                </tr>
            </thead>
            <tbody>
                {
                    data.map((source) => <SourceEntry key={source.uuid} source={source} setHasToReload={setHasToReload} setData={setData} />)
                }
            </tbody>
        </table>
    </div>;
}

function pastedURL(url: string): Source[] {
    const source = parseUrl(url);
    const data = loadSourceData();
    if (!source) return data;
    data.push(source);
    saveSourceData(data);
    return [...data];
}

function SourceEntry({ source, setHasToReload, setData }: { source: Source, setHasToReload: React.Dispatch<React.SetStateAction<boolean>>, setData: React.Dispatch<React.SetStateAction<Source[]>> }) {

    const titleInputRef = useRef<HTMLInputElement>(null);
    const authorInputRef = useRef<HTMLInputElement>(null);
    const pageNameInputRef = useRef<HTMLInputElement>(null);

    let timeoutId: number | null = null;

    const onTimeout = () => {
        if (timeoutId) {
            clearTimeout(timeoutId);
        }
        const data = loadSourceData();
        const existingSource = data.find(s => s.uuid === source.uuid);
        if (!existingSource) return;
        existingSource.title = source.title;
        existingSource.author = source.author;
        existingSource.pageName = source.pageName;
        saveSourceData(data);
        setHasToReload(true);
        console.log("Saved changes for source:", existingSource);
    }

    const onChange = () => {
        if (timeoutId) {
            clearTimeout(timeoutId);
        }
        timeoutId = setTimeout(onTimeout, 1000);
    }

    const pageNameChanged = () => {
        source.pageName = pageNameInputRef.current?.value || "";
        onChange();
    }

    const authorChanged = () => {
        source.author = authorInputRef.current?.value || "";
        onChange();
    };

    const titleChanged = () => {
        source.title = titleInputRef.current?.value || "";
        onChange();
    };

    return <tr key={source.uuid}>
        <td><span>{source.url}</span></td>
        <td><input onChange={titleChanged} onBlur={onTimeout} ref={(e) => { titleInputRef.current = e; if (e) e.value = source.title || ""; }} /></td>
        <td><input onChange={authorChanged} onBlur={onTimeout} ref={(e) => { authorInputRef.current = e; if (e) e.value = source.author || ""; }} /></td>
        <td><input onChange={pageNameChanged} onBlur={onTimeout} ref={(e) => { pageNameInputRef.current = e; if (e) e.value = source.pageName || ""; }} /></td>
        <td>
            <span>{dayjs(source.dateOfPage).format("DD.MM.YYYY")}</span>
        </td>
        <td>
            <div>
                <span>{dayjs(source.dateLastVisited).format("DD.MM.YYYY")}</span>
                <button className="updateLastDateVisited" onClick={() => updateLastDateVisited(source.uuid, setData)}>Update</button>
            </div>
        </td>
        <td>
            <button className="open" onClick={() => { open(source.url, "_blank") }}>Open</button>
            <button onClick={() => deleteSource(source.uuid, setData)}>Delete</button>
        </td>
    </tr>
}

function deleteSource(uuid: string, setData: React.Dispatch<React.SetStateAction<Source[]>>) {
    const data = loadSourceData();
    const index = data.findIndex(s => s.uuid === uuid);
    if (index === -1) return;
    data.splice(index, 1);
    saveSourceData(data);
    setData([...data]);
}

function updateLastDateVisited(uuid: string, setData: React.Dispatch<React.SetStateAction<Source[]>>) {
    const data = loadSourceData();
    const source = data.find(s => s.uuid === uuid);
    if (!source) return;
    source.dateLastVisited = new Date().toISOString();
    saveSourceData(data);
    setData([...data]);
}