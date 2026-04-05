import Image from '@tiptap/extension-image';

export const ResizableImage = Image.extend({
  name: 'resizableImage',

  addAttributes() {
    return {
      ...Image.config.addAttributes?.(),
      width: {
        default: '50%',
        parseHTML: (element) => element.style.width || element.getAttribute('width') || '50%',
        renderHTML: (attributes) => ({
          style: `width: ${attributes.width}; max-width: 100%; height: auto;`,
          width: attributes.width,
        }),
      },
      align: {
        default: 'left',
        parseHTML: (element) => {
          const style = element.style.cssText || '';
          if (style.includes('float: right')) return 'right';
          if (style.includes('float: left')) return 'left';
          return 'left';
        },
        renderHTML: (attributes) => ({
          style: `width: ${attributes.width || '50%'}; max-width: 100%; height: auto; float: ${attributes.align || 'left'}; clear: both;`,
        }),
      },
    };
  },
});