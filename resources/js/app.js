import './bootstrap';

document.addEventListener('alpine:init', () => {
    Alpine.data('localPreview', (kind) => ({
        preview: '', filename: '', error: '', mime: '',
        async select(event) {
            this.error = '';
            if (this.preview) URL.revokeObjectURL(this.preview);
            this.preview = ''; this.filename = ''; this.mime = '';
            const file = event.target.files[0];
            if (!file) return;
            const allowed = kind === 'photo'
                ? ['image/jpeg', 'image/png', 'image/webp']
                : ['application/pdf', 'image/jpeg', 'image/png'];
            const limit = (kind === 'photo' ? 2 : 5) * 1024 * 1024;
            if (!allowed.includes(file.type) || file.size > limit) {
                this.error = 'Format berkas atau ukuran tidak sesuai ketentuan.';
                event.target.value = '';
                return;
            }
            this.filename = file.name;
            this.mime = file.type;
            this.preview = URL.createObjectURL(file);

        },
        destroy() { if (this.preview) URL.revokeObjectURL(this.preview); },
    }));
});
