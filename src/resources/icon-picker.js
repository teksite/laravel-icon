import iconList from "./icons.json" assert { type: "json" };

export default function iconSetter(list = null) {
    const iconsObject = list ?? iconList;
    const iconSelector = document.querySelectorAll('i.tkicon');
    iconSelector.forEach(item => {
        const icon = item.getAttribute('data-icon');
        const iconPath = iconsObject[icon];
        if (iconPath) {
            const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            let attributes = {
                'title': item.getAttribute('title'),
                'x': item.getAttribute('x') ?? '0px',
                'y': item.getAttribute('y') ?? '0px',
                'width': item.getAttribute('width') ?? item.getAttribute('size') ?? '24',
                'height': item.getAttribute('height') ?? item.getAttribute('size') ?? '24',
                'viewBox': item.getAttribute('viewBox') ?? '0 0 24 24',
                'class': item.classList.toString() + " " + icon,
                'stroke-width': item.getAttribute('stroke-width') ?? '1',
                'stroke-linecap': item.getAttribute('stroke-linecap') ?? "round",
                "stroke-linejoin": item.getAttribute('stroke-linejoin') ?? "round"
            };
            Array.from(item.attributes).forEach(attr => {
                if (!attributes[attr.name]) {
                    attributes[attr.name] = attr.value;
                }
            });
            for (const [key, value] of Object.entries(attributes)) {
                if (value) svg.setAttribute(key, value);
            }
            svg.innerHTML = iconPath;
            item.parentNode.replaceChild(svg, item);
        }
    });
}
