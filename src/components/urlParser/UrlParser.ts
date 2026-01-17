import type { Source } from "../dataLoader/DataLoader";

type UrlParseResult = {
    title: string;
    creationDate: string;
    author: string;
    pageName: string;
    trustLevelOutOf100: number;
};

type Reponse<T> = {
    status: "success" | "error";
    data: T;
    message?: string;
}

export async function parseUrl(url: string): Promise<Source | null> {
    const result = await (await fetch("http://localhost:2222/sourcesManager/crawlWebsite.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ websiteURL: url })
    })).json() as Reponse<UrlParseResult>;
    if (result) {
        if (result.status == "success") {
            return {
                url: url,
                title: result.data.title,
                author: result.data.author,
                pageName: result.data.pageName,
                dateOfPage: result.data.creationDate,
                dateLastVisited: new Date().toISOString(),
                uuid: crypto.randomUUID()
            };
        } else {
            console.error("Error parsing URL:", result.message);
            return null;
        }
    }
    return null;
}