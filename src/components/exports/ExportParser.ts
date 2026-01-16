import type { Source } from "../dataLoader/DataLoader";

const EXPORT_VARS = [
    "URL",
    "TITLE",
    "WEBSITE_NAME",
    "LAST_VISITED",
    "ARTICLE_CREATED_AT",
    "AUTHOR",
    "INDEX"
]

export type Lang = "en" | "de";

export default function parseExportFormat(format: string, data: Source[], lang: Lang): string {
    let result = "";
    data.forEach((source, index) => {
        let entry = format;
        [...EXPORT_VARS, ...EXPORT_VARS.map(e => e + "_HIDE_UNKNOWN")].forEach((variable) => {
            let replacement = "";
            switch (variable.replace("_HIDE_UNKNOWN", "")) {
                case "URL":
                    replacement = source.url;
                    break;
                case "TITLE":
                    replacement = source.title;
                    break;
                case "LAST_VISITED":
                    replacement = source.dateLastVisited;
                    break;
                case "ARTICLE_CREATED_AT":
                    replacement = source.dateOfPage;
                    break;
                case "AUTHOR":
                    replacement = source.author;
                    break;
                case "INDEX":
                    replacement = (index + 1).toString();
                    break;
            }
            if (variable.endsWith("_HIDE_UNKNOWN") && (!replacement || replacement === "Unknown")) {
                entry = entry.replaceAll(`{${variable}}`, replacement);
            } else {
                entry = entry.replaceAll(`{${variable}}`, replacement || (lang === "en" ? "Unknown" : "Unbekannt"));
            }
        });
        result += entry + "\n";
    });
    result = result.trimEnd();
    return result;
}