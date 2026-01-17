import type { Source } from "../dataLoader/DataLoader";
import dayjs from "dayjs";

const EXPORT_VARS = [
    "URL",
    "TITLE",
    "WEBSITE_NAME",
    "LAST_VISITED",
    "ARTICLE_CREATED_AT",
    "AUTHOR",
    "AUTHOR_SURNAME",
    "AUTHOR_FIRSTNAME",
    "AUTHOR_INITIALE",
    "AUTHOR_INITIALE_BOTH",
    "AUTHOR_INITIALE_FIRSTNAME",
    "AUTHOR_INITIALE_LASTNAME",
    "INDEX",
    /{LAST_VISITED;(.*?)}/g,
    /{ARTICLE_CREATED_AT;(.*?)}/g
]

export type Lang = "en" | "de";

export default function parseExportFormat(format: string, data: Source[], lang: Lang): string {
    let result = "";
    data.forEach((source, index) => {
        let entry = "" + format;
        [...EXPORT_VARS, ...EXPORT_VARS.filter(e => typeof e == "string").map(e => e + "_HIDE_UNKNOWN")].forEach((variable) => {
            let replacement = "";
            if (variable instanceof RegExp) {
                const match = entry.match(variable);
                if (match) {
                    const dateFormat = match[0].split(";")[1].slice(0, -1);
                    let dateValue = "";
                    if (variable.source.startsWith("{LAST_VISITED")) {
                        dateValue = source.dateLastVisited;
                    } else if (variable.source.startsWith("{ARTICLE_CREATED_AT")) {
                        dateValue = source.dateOfPage;
                    }
                    if (dateValue && dateValue !== "Unknown") {
                        replacement = dayjs(new Date(dateValue)).format(dateFormat);
                        console.log("Formatted date:", dateValue, "to", replacement + " using format", dateFormat);
                    }
                    entry = entry.replaceAll(match[0], replacement || (lang === "en" ? "Unknown" : "Unbekannt"));
                }
            } else {
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
                    case "AUTHOR_SURNAME":
                        replacement = source.author.split(" ").slice(-1)[0];
                        break;
                    case "AUTHOR_FIRSTNAME":
                        replacement = source.author.split(" ").slice(0, -1).join(" ");
                        break;
                    case "AUTHOR_INITIALE_BOTH":
                        {
                            const names = source.author.split(" ");
                            replacement = names.map(n => n.charAt(0).toUpperCase() + ".").join(" ");
                        }
                        break;
                    case "AUTHOR_INITIALE_FIRSTNAME":
                        {
                            const names = source.author.split(" ");
                            replacement = names.slice(0, -1).map(n => n.charAt(0).toUpperCase() + ".").join(" ");
                        }
                        break;
                    case "AUTHOR_INITIALE_LASTNAME":
                        {
                            const names = source.author.split(" ");
                            replacement = names.slice(-1)[0].charAt(0).toUpperCase() + ".";
                        }
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
            }
        });
        result += entry + "\n";
    });
    result = result.trimEnd();
    return result;
}