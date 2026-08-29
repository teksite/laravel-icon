/**
 * @typedef {'outline' | 'solid'} IconType
 * @typedef {Record<string, string>} IconMap
 */

import outlineList from "./outline.json" assert {type: "json"};
import solidList from "./solid.json" assert {type: "json"};

const SVG_NS = "http://www.w3.org/2000/svg";
const ICON_SELECTOR = "i.tkicon";

const DEFAULTS = Object.freeze({
    SIZE: "24",
    VIEW_BOX: "0 0 24 24",
    STROKE_WIDTH: "1",
    STROKE_LINECAP: "round",
    STROKE_LINEJOIN: "round"
});

/**
 * Cache parsed icon lists.
 *
 * @type {Map<string, string>}
 */
const iconCache = new Map();

/**
 * @param {IconMap | null} [customList]
 * @returns {void}
 */
export default function iconSetter(customList = null) {
    const icons = document.querySelectorAll(ICON_SELECTOR);

    if (icons.length === 0) return;

    for (const icon of icons) {
        replaceIcon(icon, customList);
    }
}

/**
 * @param {HTMLElement} icon
 * @param {IconMap | null} customList
 * @returns {void}
 */
function replaceIcon(icon, customList) {
    const name = icon.dataset.icon;

    if (!name) {
        console.warn("Missing data-icon attribute");
        return;
    }

    const type = icon.dataset.type ?? 'outline'

    const pathData = getIcon(name, type, customList);

    if (!pathData) {
        console.warn(`Icon "${name}" not found`);
        return;
    }

    try {
        const svg = createSvgElement(icon, name, pathData);
        icon.replaceWith(svg);
    } catch (error) {
        console.error(`Failed to replace icon "${name}":`, error);
    }
}

/**
 * @param {string} name
 * @param {IconType} type
 * @param {IconMap | null} customList
 * @returns {string | undefined}
 */
function getIcon(name, type, customList) {
    if (customList?.[name]) return customList[name];

    const cacheKey = `${type}:${name}`;

    const cached = iconCache.get(cacheKey);

    if (cached !== undefined) return cached;

    const list = type === "solid"
        ? solidList
        : outlineList;

    const pathData = list[name];

    if (pathData) iconCache.set(cacheKey, pathData);

    return pathData;
}

/**
 * @param {HTMLElement} original
 * @param {string} iconName
 * @param {string} pathData
 * @returns {SVGElement}
 */
function createSvgElement(original, iconName, pathData) {
    const svg = document.createElementNS(SVG_NS, "svg");

    copyAttributes(original, svg, iconName);

    svg.innerHTML = pathData;

    return svg;
}

/**
 * @param {HTMLElement} original
 * @param {SVGElement} svg
 * @param {string} iconName
 * @returns {void}
 */
function copyAttributes(original, svg, iconName) {
    const attributes = original.attributes;

    for (let i = 0; i < attributes.length; i++) {
        const attr = attributes[i];
        const name = attr.name;

        if (name === "data-icon" || name === "data-type") continue;

        svg.setAttribute(name, attr.value);
    }

    const size = original.getAttribute("size") ?? DEFAULTS.SIZE;

    if (!svg.hasAttribute("width")) {
        svg.setAttribute("width", original.getAttribute("width") ?? size);
    }

    if (!svg.hasAttribute("height")) {
        svg.setAttribute("height", original.getAttribute("height") ?? size);
    }

    if (!svg.hasAttribute("viewBox")) {
        svg.setAttribute("viewBox", original.getAttribute("viewBox") ?? DEFAULTS.VIEW_BOX);
    }

    if (!svg.hasAttribute("stroke-width")) {
        svg.setAttribute("stroke-width", original.getAttribute("stroke-width") ?? DEFAULTS.STROKE_WIDTH);
    }

    if (!svg.hasAttribute("stroke-linecap")) {
        svg.setAttribute("stroke-linecap", original.getAttribute("stroke-linecap") ?? DEFAULTS.STROKE_LINECAP);
    }

    if (!svg.hasAttribute("stroke-linejoin")) {
        svg.setAttribute("stroke-linejoin", original.getAttribute("stroke-linejoin") ?? DEFAULTS.STROKE_LINEJOIN);
    }

    svg.classList.add(iconName);
}
