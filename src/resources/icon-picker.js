/**
 * @typedef {'outline' | 'solid'} IconType
 * @typedef {Record<string, string>} IconMap
 */

import outlineList from "./outline.json" assert { type: "json" };
import solidList from "./solid.json" assert { type: "json" };

const SVG_NS = "http://www.w3.org/2000/svg";
const DEFAULTS = {
    SIZE: "24",
    VIEW_BOX: "0 0 24 24",
    STROKE_WIDTH: "1",
    STROKE_LINECAP: "round",
    STROKE_LINEJOIN: "round"
};

/**
 * @param {IconMap | null} [customList]
 * @returns {void}
 */
export default function iconSetter(customList = null) {
    const icons = document.querySelectorAll('i.tkicon');
    if (!icons.length) return;

    icons.forEach(icon => {
        try {
            /** @type {IconType} */
            const type = icon.getAttribute('data-type') ?? 'outline';
            const name = icon.getAttribute('data-icon');

            if (!name) {
                console.warn('Missing data-icon attribute');
                return;
            }

            const path = customList?.[name] ?? (type === 'solid' ? solidList[name] : outlineList[name]);
            if (!path) {
                console.warn(`Icon "${name}" not found`);
                return;
            }

            const svg = createSvgElement(icon, name, path);
            icon.parentNode?.replaceChild(svg, icon);
        } catch (err) {
            console.error('Icon replacement failed:', err);
        }
    });
}

/**
 * @param {HTMLElement} original
 * @param {string} iconName
 * @param {string} pathData
 * @returns {SVGElement}
 */
function createSvgElement(original, iconName, pathData) {
    const svg = document.createElementNS(SVG_NS, 'svg');
    const attrs = buildAttributes(original, iconName);

    Object.entries(attrs).forEach(([key, value]) => {
        if (value != null) svg.setAttribute(key, value);
    });

    svg.innerHTML = pathData;
    return svg;
}

/**
 * @param {HTMLElement} el
 * @param {string} iconName
 * @returns {Record<string, string | undefined | null>}
 */
function buildAttributes(el, iconName) {
    const get = (attr, def) => el.getAttribute(attr) ?? def;

    const base = {
        title: get('title'),
        x: get('x', '0px'),
        y: get('y', '0px'),
        width: get('width') ?? get('size', DEFAULTS.SIZE),
        height: get('height') ?? get('size', DEFAULTS.SIZE),
        viewBox: get('viewBox', DEFAULTS.VIEW_BOX),
        class: `${el.classList.toString()} ${iconName}`,
        'stroke-width': get('stroke-width', DEFAULTS.STROKE_WIDTH),
        'stroke-linecap': get('stroke-linecap', DEFAULTS.STROKE_LINECAP),
        'stroke-linejoin': get('stroke-linejoin', DEFAULTS.STROKE_LINEJOIN)
    };

    for (const attr of Array.from(el.attributes)) {
        const name = attr.name;
        if (!base[name] && name !== 'data-type' && name !== 'data-icon') {
            base[name] = attr.value;
        }
    }

    return base;
}
