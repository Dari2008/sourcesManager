
var LOADED_DATA: (Source[]) | null = null;

export function loadSourceData(): Source[] {
    if (LOADED_DATA) return LOADED_DATA;
    const data = localStorage.getItem("sourceData") ?? "[]";
    return LOADED_DATA = JSON.parse(data);
}

export function saveSourceData(data: Source[]) {
    LOADED_DATA = data;
    localStorage.setItem("sourceData", JSON.stringify(data));
}

export type Source = {
    url: string;
    title: string;
    author: string;
    pageName: string;
    dateOfPage: string;
    dateLastVisited: string;
    uuid: string;
}