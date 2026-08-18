

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.documentUploadForm = function documentUploadForm({
    yearFolderId = null,
    folderId = null,
    folders = [],
    endpointTemplate = '',
} = {}) {
    return {
        yearFolderId: yearFolderId ? String(yearFolderId) : '',
        folderId: folderId ? String(folderId) : '',
        folders: Array.isArray(folders) ? folders : [],
        loading: false,
        error: '',
        endpointTemplate,

        async onYearChange() {
            this.folderId = '';
            this.error = '';
            this.folders = [];

            if (!this.yearFolderId) {
                return;
            }

            this.loading = true;

            try {
                const url = this.endpointTemplate.replace('__YEAR__', encodeURIComponent(this.yearFolderId));
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Gagal memuat folder untuk tahun yang dipilih.');
                }

                const payload = await response.json();
                this.folders = Array.isArray(payload.folders) ? payload.folders : [];
            } catch (e) {
                this.error = e instanceof Error ? e.message : 'Gagal memuat folder.';
                this.folders = [];
            } finally {
                this.loading = false;
            }
        },
    };
};

window.documentMetadataForm = function documentMetadataForm({
    area = '',
    subtype = '',
    subtypesMap = {},
} = {}) {
    return {
        area: area || '',
        subtype: subtype || '',
        subtypesMap,
        subtypes() {
            return this.subtypesMap[this.area] || {};
        },
        onAreaChange() {
            const options = this.subtypes();
            if (!Object.prototype.hasOwnProperty.call(options, this.subtype)) {
                this.subtype = '';
            }
        },
    };
};

window.documentFilterBar = function documentFilterBar({
    area = '',
    subtype = '',
    subtypesMap = {},
} = {}) {
    return {
        area: area || '',
        subtype: subtype || '',
        subtypesMap,
        subtypes() {
            return this.subtypesMap[this.area] || {};
        },
        onAreaChange() {
            const options = this.subtypes();
            if (!Object.prototype.hasOwnProperty.call(options, this.subtype)) {
                this.subtype = '';
            }
        },
    };
};

Alpine.start();
