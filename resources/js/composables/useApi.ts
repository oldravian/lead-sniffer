export function getCsrfHeaders(): Record<string, string> {
    if (typeof document === 'undefined') {
        return {};
    }

    const metaToken = document.querySelector<HTMLMetaElement>(
        'meta[name="csrf-token"]',
    )?.content;

    if (metaToken) {
        return {
            'X-CSRF-TOKEN': metaToken,
        };
    }

    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    if (!match) {
        return {};
    }

    return {
        'X-XSRF-TOKEN': decodeURIComponent(match[1]),
    };
}

export function useApi() {
    return {
        getCsrfHeaders,
    };
}
