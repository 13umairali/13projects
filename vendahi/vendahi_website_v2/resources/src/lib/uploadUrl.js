/**
 * Tenant-aware public URL for an uploaded file.
 *
 * Uploads live under a per-tenant folder — `upload_path()` on the server,
 * exposed to the SPA as window.__uploadPath by layouts/next.blade.php (the
 * legacy app read the same value into Vue.prototype.$uploadPath / $imgUrl).
 * On a single-tenant install it is plain "images".
 *
 * Every page copied from the stocky reference hard-codes '/images/<folder>/…';
 * ALL of them must go through this helper instead (migration rule 9).
 *
 *   uploadUrl('brands', row.image)       -> /<tenant-path>/brands/<file>
 *   uploadUrl('avatar', user.avatar)     -> /<tenant-path>/avatar/<file>
 *   uploadUrl('', settings.logo)         -> /<tenant-path>/<file>
 */
export function uploadPath() {
    const p = typeof window !== 'undefined' && window.__uploadPath ? String(window.__uploadPath) : 'images';
    return p.replace(/^\/+|\/+$/g, '');
}

export function uploadUrl(folder, file) {
    if (!file) return '';
    if (/^(https?:)?\/\//.test(file) || String(file).startsWith('data:')) return file;
    const parts = [uploadPath()];
    if (folder) parts.push(String(folder).replace(/^\/+|\/+$/g, ''));
    parts.push(String(file).replace(/^\/+/, ''));
    return '/' + parts.join('/');
}

export default uploadUrl;
