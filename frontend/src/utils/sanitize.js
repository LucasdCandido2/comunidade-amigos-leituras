const ALLOWED_TAGS = [
    'p', 'br', 'strong', 'em', 's', 'u',
    'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
    'ul', 'ol', 'li',
    'a', 'img',
    'blockquote', 'pre', 'code',
    'hr', 'span', 'div',
    'table', 'thead', 'tbody', 'tr', 'th', 'td',
    'mark'
];

const ALLOWED_ATTR = [
    'href', 'src', 'alt', 'title', 'class', 'id',
    'width', 'height', 'align',
    'target', 'rel'
];

export const sanitizeHtml = (html) => {
    if (!html) return '';
    
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    
    const sanitizeNode = (node) => {
        if (node.nodeType === Node.TEXT_NODE) {
            return node.textContent;
        }
        
        if (node.nodeType !== Node.ELEMENT_NODE) {
            return '';
        }
        
        const tagName = node.tagName.toLowerCase();
        
        if (!ALLOWED_TAGS.includes(tagName)) {
            return Array.from(node.childNodes).map(sanitizeNode).join('');
        }
        
        let attrs = '';
        for (const attr of node.attributes) {
            if (ALLOWED_ATTR.includes(attr.name)) {
                let value = attr.value;
                
                if (attr.name === 'href' || attr.name === 'src') {
                    if (!value.startsWith('http') && !value.startsWith('mailto:') && !value.startsWith('/')) {
                        continue;
                    }
                }
                
                attrs += ` ${attr.name}="${escapeHtml(value)}"`;
            }
        }
        
        const children = Array.from(node.childNodes).map(sanitizeNode).join('');
        return `<${tagName}${attrs}>${children}</${tagName}>`;
    };
    
    return Array.from(doc.body.childNodes).map(sanitizeNode).join('');
};

const escapeHtml = (str) => {
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

export const truncateHtml = (html, maxLength = 100) => {
    if (!html) return '';
    
    const clean = sanitizeHtml(html);
    const div = document.createElement('div');
    div.innerHTML = clean;
    
    const text = div.textContent || div.innerText || '';
    
    if (text.length <= maxLength) return clean;
    
    return text.substring(0, maxLength).trim() + '...';
};