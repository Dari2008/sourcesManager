import type { Source } from "../dataLoader/DataLoader";

export function parseUrl(url: string): Source | null {
    return {
        url: url,
        author: "Test Author",
        title: "Test Title",
        dateLastVisited: new Date("2026-01-13T20:13:55.762Z").toISOString(),
        dateOfPage: new Date().toISOString(),
        uuid: crypto.randomUUID()
    }
}