import APA_STYLE from "./formats/apa-style.txt?raw";
import MLA_STYLE from "./formats/mla-style.txt?raw";
import CHICAGO_STYLE from "./formats/chicago-style.txt?raw";
import HARVARD_STYLE from "./formats/harvard-style.txt?raw";
import IEEE_STYLE from "./formats/ieee-style.txt?raw";
import DIN_ISO_690_STYLE from "./formats/din-iso-690-style.txt?raw";
import { useEffect, useRef, useState, type DialogHTMLAttributes } from "react";
import type { Source } from "../dataLoader/DataLoader";
import parseExportFormat from "./ExportParser";
import "./ExportDialog.scss";

type Props = React.DetailedHTMLProps<DialogHTMLAttributes<HTMLDialogElement>, HTMLDialogElement> & {
    sources: Source[];
    buttonRef?: React.RefObject<HTMLButtonElement | null>;
}

export default function ExportDialog({ sources, buttonRef, ...dialogProps }: Props) {

    const [format, setFormat] = useState<ExportFormat>("json");
    const exportFormatSelectRef = useRef<HTMLSelectElement>(null);
    const [state, setState] = useState<"idle" | "exporting" | "done">("idle");
    const [exportedData, setExportedData] = useState<string>("");
    const customFormatRef = useRef<string>(null);
    const dialogRef = useRef<HTMLDialogElement>(null);

    function openDialog() {
        setState("exporting");
        dialogRef.current?.showModal();
    }

    function closeDialog() {
        dialogRef.current?.close();
        setState("idle");
        setExportedData("");
    }

    useEffect(() => {
        if (buttonRef?.current) {
            buttonRef.current.onclick = openDialog;
        }
    }, [buttonRef]);

    return <dialog className="exportDialog" data-state={state} {...dialogProps} ref={dialogRef}>
        {
            state === "exporting"
                ?
                <>
                    <h2>Export Data</h2>
                    <label htmlFor="exportFormat">Select Export Format:</label>
                    <select name="exportFormat" id="exportFormat" onChange={() => setFormat(exportFormatSelectRef.current?.value as ExportFormat)} ref={exportFormatSelectRef}>
                        <option value="json">JSON</option>
                        <option value="csv">CSV</option>
                        <option value="markdown-table">Markdown-table</option>
                        <option value="markdown-list-w-bold">Markdown List with Highlight</option>
                        <option value="apa">APA Style</option>
                        <option value="mla">MLA Style</option>
                        <option value="chicago">Chicago (Notes & Bibliography)</option>
                        <option value="harvard">Harvard</option>
                        <option value="ieee">IEEE</option>
                        <option value="dinISO690">DIN ISO 690</option>
                        <option value="custom">Custom Format</option>
                    </select>
                    {format == "custom" && <>
                        <label htmlFor="exportFormat">Custom Format:</label>
                        <div className="exportFormat" contentEditable={true} onInput={e => customFormatRef.current = (e.target as HTMLDivElement).textContent || ""}>{customFormatRef.current ?? ""}</div>
                    </>}
                    <div className="buttons">
                        <button className="cancel" onClick={closeDialog}>Cancel</button>
                        <button className="export" onClick={() => { setExportedData(exportData(sources, format, customFormatRef.current || "")); setState("done") }}>Export</button>
                    </div>
                </>
                :
                <>
                    <h2>Exported</h2>
                    <p>Your data has been exported successfully.</p>
                    <div id="exportedData" contentEditable={true} suppressContentEditableWarning={true}>{exportedData}</div>
                    <div className="buttons">
                        <button className="close" onClick={closeDialog}>Close</button>
                        <button className="copy" onClick={() => {
                            navigator.clipboard.writeText(exportedData);
                        }}>Copy</button>
                    </div>
                </>
        }
    </dialog>;
}

function exportData(sources: Source[], format: ExportFormat, customFormat: string): string {
    switch (format) {
        case "json":
            return JSON.stringify(sources, null, 4);
        case "csv":
            {
                const headers = ["URL", "Title", "Author", "Date of Page", "Date Last Visited"];
                const rows = sources.map(source => [
                    `"${source.url.replace(/"/g, '""')}"`,
                    `"${source.title.replace(/"/g, '""')}"`,
                    `"${source.author.replace(/"/g, '""')}"`,
                    `"${source.dateOfPage}"`,
                    `"${source.dateLastVisited}"`
                ].join(","));
                return [headers.join(","), ...rows].join("\n");
            }
        case "markdown-table":
            {
                const headers = ["| URL | Title | Author | Date of Page | Date Last Visited |", "|---|---|---|---|---|"];
                const rows = sources.map(source => `| [${source.title}](${source.url}) | ${source.title} | ${source.author} | ${source.dateOfPage} | ${source.dateLastVisited} |`);
                return [...headers, ...rows].join("\n");
            }
        case "markdown-list-w-bold":
            {
                const rows = sources.map(source => `- **[${source.title}](${source.url})** by ${source.author} (Page Date: ${source.dateOfPage}, Last Visited: ${source.dateLastVisited})`);
                return rows.join("\n");
            }
        case "apa":
            return parseExportFormat(APA_STYLE, sources, "en");
        case "mla":
            return parseExportFormat(MLA_STYLE, sources, "en");
        case "chicago":
            return parseExportFormat(CHICAGO_STYLE, sources, "en");
        case "harvard":
            return parseExportFormat(HARVARD_STYLE, sources, "en");
        case "ieee":
            return parseExportFormat(IEEE_STYLE, sources, "en");
        case "dinISO690":
            return parseExportFormat(DIN_ISO_690_STYLE, sources, "en");
        case "custom":
            return parseExportFormat(customFormat, sources, "en");
        default:
            return "";
    }
}

export type ExportFormat = "json" | "csv" | "markdown-table" | "markdown-list-w-bold" | "apa" | "mla" | "chicago" | "harvard" | "ieee" | "dinISO690" | "custom";

