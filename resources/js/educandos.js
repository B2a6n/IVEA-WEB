// Slide-over of the Directorio de Educandos: loads one educando's detail (JSON) and renders it with x-text only.

const LOAD_ERROR = 'No se pudo cargar el progreso del educando. Intenta de nuevo.';

const SESSION_EXPIRED = [401, 419];

export default function educandoPanel() {
    // Outside Alpine's reactive state: plain handles, not UI data.
    let controller = null;
    let trigger = null;

    return {
        open: false,
        loading: false,
        error: null,
        detail: null,

        async show(url, origin = null) {
            controller?.abort();
            const current = new AbortController();
            controller = current;
            trigger = origin;

            this.open = true;
            this.loading = true;
            this.error = null;
            this.detail = null;

            try {
                const response = await fetch(url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: current.signal,
                });
                // Session expired (401) or stale CSRF/session (419): reload so the login page takes over.
                if (SESSION_EXPIRED.includes(response.status)) {
                    window.location.reload();
                    return;
                }
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                this.detail = await response.json();
            } catch (exception) {
                if (exception.name !== 'AbortError') {
                    this.error = LOAD_ERROR;
                }
            } finally {
                if (controller === current) {
                    this.loading = false;
                }
            }
        },

        close() {
            controller?.abort();
            this.open = false;
            this.loading = false;
            trigger?.focus();
            trigger = null;
        },
    };
}
