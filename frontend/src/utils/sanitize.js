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
    'width', 'height', 'align', 'style',
    'target', 'rel'
];

const ALLOWED_CSS_PROPS = [
    'float', 'width', 'max-width', 'height', 'max-height', 
    'margin', 'border-radius', 'display', 'vertical-align'
];

const ALLOWED_IMG_CLASSES = ['image-left', 'image-right', 'editor-image'];

const ALLOWED_SPAN_CLASSES = ['spoiler-tag'];

const isAllowedUrl = (url) => {
    if (!url) return false;
    const trimmed = url.trim();
    return trimmed.startsWith('http') || 
           trimmed.startsWith('/') || 
           trimmed.startsWith('data:') ||
           trimmed.startsWith('assets/') ||
           trimmed.startsWith('storage/');
};

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
                    if (!isAllowedUrl(value)) {
                        continue;
                    }
                }
                
                if (attr.name === 'class' && tagName === 'img') {
                    const classes = value.split(' ').filter(c => ALLOWED_IMG_CLASSES.includes(c));
                    if (classes.length > 0) {
                        attrs += ` class="${classes.join(' ')}"`;
                    }
                    continue;
                }
                
                if (attr.name === 'class' && tagName === 'span') {
                    const classes = value.split(' ').filter(c => ALLOWED_SPAN_CLASSES.includes(c));
                    if (classes.length > 0) {
                        attrs += ` class="${classes.join(' ')}"`;
                        if (value.includes('spoiler-tag')) {
                            const dataSpoiler = node.getAttribute('data-spoiler');
                            if (dataSpoiler) {
                                attrs += ` data-spoiler="${escapeHtml(dataSpoiler)}"`;
                            }
                        }
                    }
                    continue;
                }
                
                if (attr.name === 'data-spoiler') {
                    attrs += ` data-spoiler="${escapeHtml(value)}"`;
                    continue;
                }
                
                if (attr.name === 'style') {
                    const styleParts = value.split(';').filter(s => {
                        const [key] = s.split(':').map(k => k.trim());
                        return ALLOWED_CSS_PROPS.some(prop => key.includes(prop));
                    });
                    if (styleParts.length > 0) {
                        attrs += ` style="${escapeHtml(styleParts.join('; '))}"`;
                    }
                    continue;
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
    
    const truncatedText = text.substring(0, maxLength).trim();
    const truncatedHtml = document.createElement('div');
    truncatedHtml.textContent = truncatedText + '...';
    
    return truncatedHtml.innerHTML;
};

export const extractTextWithImages = (html, maxLength = 100) => {
    if (!html) return '';
    
    const clean = sanitizeHtml(html);
    const div = document.createElement('div');
    div.innerHTML = clean;
    
    const images = Array.from(div.querySelectorAll('img'));
    const imageHtml = images.map(img => img.outerHTML).join(' ');
    
    const text = div.textContent || div.innerText || '';
    
    if (text.length <= maxLength) {
        return imageHtml + clean;
    }
    
    const truncatedText = text.substring(0, maxLength).trim();
    const result = document.createElement('div');
    result.innerHTML = imageHtml + '<span>' + truncatedText + '...</span>';
    
    return result.innerHTML;
};