import { useEffect, useState } from "react";
import "./Main.scss";
import { loadSourceData, parseUrl } from "../../components";
import { saveSourceData, type Source } from "../../components/dataLoader/DataLoader";

import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";
dayjs.extend(customParseFormat);

export default function Main() {
    const [data, setData] = useState<Source[]>(() => loadSourceData());

    useEffect(() => {
        window.addEventListener("paste", (e) => {
            const items = e.clipboardData?.items;
            if (!items) return;
            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                if (item.kind !== "string") continue;
                if (item.type !== "text/plain") continue;
                item.getAsString((s) => {
                    if (!s.startsWith("http")) return;
                    pastedURL(s, setData);
                });
            }
        });
    }, []);

    return <div className="main">
        <table>
            <thead>
                <tr>
                    <th>URL</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Date of Page</th>
                    <th>Date Last Visited</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                {
                    data.map((source) => <SourceEntry key={source.uuid} source={source} setData={setData} />)
                }
            </tbody>
        </table>
    </div>;
}

function pastedURL(url: string, setData: React.Dispatch<React.SetStateAction<Source[]>>) {
    const source = parseUrl(url);
    if (!source) return;
    const data = loadSourceData();
    data.push(source);
    saveSourceData(data);
    setData(data);
}

function SourceEntry({ source, setData }: { source: Source, setData: React.Dispatch<React.SetStateAction<Source[]>> }) {

    const authorChanged = () => {
        const data = loadSourceData();
        const s = data.find(s => s.uuid === source.uuid);
        if (!s) return;
        s.author = (document.activeElement as HTMLInputElement).value;
        saveSourceData(data);
        setData(data);
    };

    const titleChanged = () => {
        const data = loadSourceData();
        const s = data.find(s => s.uuid === source.uuid);
        if (!s) return;
        s.title = (document.activeElement as HTMLInputElement).value;
        saveSourceData(data);
        setData(data);
    };

    return <tr key={source.uuid}>
        <td>{source.url}</td>
        <td><input value={source.title} onChange={titleChanged} /></td>
        <td><input value={source.author} onChange={authorChanged} /></td>
        <td>{dayjs(source.dateOfPage).format("DD.MM.YYYY")}</td>
        <td>{dayjs(source.dateLastVisited).format("DD.MM.YYYY")} <button className="updateLastDateVisited" onClick={() => updateLastDateVisited(source.uuid, setData)}>Update</button></td>
        <td><button onClick={() => deleteSource(source.uuid, setData)}>Delete</button></td>
    </tr>
}

function deleteSource(uuid: string, setData: React.Dispatch<React.SetStateAction<Source[]>>) {
    const data = loadSourceData();
    const index = data.findIndex(s => s.uuid === uuid);
    if (index === -1) return;
    data.splice(index, 1);
    saveSourceData(data);
    setData(data);
}

function updateLastDateVisited(uuid: string, setData: React.Dispatch<React.SetStateAction<Source[]>>) {
    const data = loadSourceData();
    const source = data.find(s => s.uuid === uuid);
    if (!source) return;
    source.dateLastVisited = new Date().toISOString();
    saveSourceData(data);
    setData(data);
}