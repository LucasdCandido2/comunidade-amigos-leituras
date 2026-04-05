import React, { useCallback, useState, useEffect } from 'react';
import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import { ResizableImage } from './extensions/ResizableImage';
import { assetService } from '../services/assetService';

const MAX_CHARS = 10000;
const MAX_IMAGES = 6;
const ALLOWED_TYPES = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];

export function RichTextEditor({ 
  content, 
  onChange, 
  placeholder = 'Digite seu texto aqui...',
  topicId = null,
  editable = true 
}) {
  const [imageCount, setImageCount] = useState(0);
  const [error, setError] = useState(null);
  const [uploading, setUploading] = useState(false);
  const [selectedImage, setSelectedImage] = useState(null);
  
  void topicId;

  const countImages = (html) => {
    const div = document.createElement('div');
    div.innerHTML = html;
    return div.querySelectorAll('img').length;
  };

  const stripHtml = (html) => {
    const div = document.createElement('div');
    div.innerHTML = html;
    return div.textContent || div.innerText || '';
  };

  const editor = useEditor({
    extensions: [
      StarterKit.configure({
        heading: false,
        codeBlock: false,
      }),
      ResizableImage.configure({
        inline: false,
        allowBase64: false,
        HTMLAttributes: {
          class: 'rich-image',
        },
      }),
      Link.configure({
        openOnClick: false,
        HTMLAttributes: {
          class: 'rich-link',
        },
      }),
      Placeholder.configure({
        placeholder,
      }),
    ],
    content: content,
    editable,
    onUpdate: ({ editor }) => {
      const html = editor.getHTML();
      const text = stripHtml(html);
      const currentImageCount = countImages(html);
      
      if (text.length > MAX_CHARS) {
        setError(`Limite de ${MAX_CHARS} caracteres excedido`);
        return;
      }
      
      setError(null);
      setImageCount(currentImageCount);
      onChange(html);
    },
  });

  useEffect(() => {
    if (editor && content !== undefined) {
      const currentHtml = editor.getHTML();
      if (content !== currentHtml) {
        editor.commands.setContent(content);
        setImageCount(countImages(content));
      }
    }
  }, [content, editor]);

  const handleImageUpload = useCallback(async (file) => {
    if (!file || !file.type.startsWith('image/')) {
      setError('Tipo de arquivo não permitido');
      return null;
    }
    
    if (!ALLOWED_TYPES.includes(file.type)) {
      setError('Formatos aceitos: PNG, JPEG, WebP, GIF');
      return null;
    }
    
    if (imageCount >= MAX_IMAGES) {
      setError(`Máximo de ${MAX_IMAGES} imagens por mensagem`);
      return null;
    }

    if (uploading) {
      return null;
    }
    
    setUploading(true);
    setError(null);
    
    try {
      const uploaded = await assetService.uploadInline(file);
      setImageCount(prev => prev + 1);
      return uploaded.url;
    } catch (err) {
      console.error('Erro ao fazer upload da imagem:', err);
      setError('Erro ao fazer upload da imagem');
      return null;
    } finally {
      setUploading(false);
    }
  }, [imageCount, uploading]);

  const handlePaste = useCallback((event) => {
    const items = event.clipboardData?.items;
    if (!items) return;

    for (const item of items) {
      if (item.type.startsWith('image/')) {
        event.preventDefault();
        const file = item.getAsFile();
        handleImageUpload(file).then(url => {
          if (url && editor) {
            editor.chain().focus().setImage({ src: url, width: '100%', align: 'left' }).run();
          }
        });
        return;
      }
    }
  }, [editor, handleImageUpload]);

  const handleDrop = useCallback((event) => {
    const files = event.dataTransfer?.files;
    if (!files || files.length === 0) return;

    for (const file of files) {
      if (file.type.startsWith('image/')) {
        event.preventDefault();
        handleImageUpload(file).then(url => {
          if (url && editor) {
            editor.chain().focus().setImage({ src: url, width: '100%', align: 'left' }).run();
          }
        });
        return;
      }
    }
  }, [editor, handleImageUpload]);

  const addImage = useCallback(() => {
    if (imageCount >= MAX_IMAGES) {
      setError(`Máximo de ${MAX_IMAGES} imagens por mensagem`);
      return;
    }
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/png,image/jpeg,image/webp,image/gif';
    input.onchange = async (e) => {
      const file = e.target.files?.[0];
      if (file) {
        const url = await handleImageUpload(file);
        if (url && editor) {
          editor.chain().focus().setImage({ src: url, width: '50%', align: 'left' }).run();
        }
      }
    };
    input.click();
  }, [editor, handleImageUpload, imageCount]);

  const setLink = useCallback(() => {
    if (!editor) return;
    
    const previousUrl = editor.getAttributes('link').href;
    const url = window.prompt('URL', previousUrl);
    
    if (url === null) return;
    
    if (url === '') {
      editor.chain().focus().extendMarkRange('link').unsetLink().run();
      return;
    }
    
    editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
  }, [editor]);

  const resizeImage = useCallback((width) => {
    if (!editor) return;
    const sizes = ['25%', '50%', '75%', '100%'];
    const currentIndex = sizes.indexOf(width) || 0;
    const nextSize = sizes[(currentIndex + 1) % sizes.length];
    editor.chain().focus().updateAttributes('image', { width: nextSize }).run();
  }, [editor]);

  const alignImage = useCallback((align) => {
    if (!editor) return;
    editor.chain().focus().updateAttributes('image', { align }).run();
  }, [editor]);

  if (!editor) return null;

  const toolbarButtons = [
    { icon: <strong>B</strong>, action: () => editor.chain().focus().toggleBold().run(), active: editor.isActive('bold'), title: 'Negrito' },
    { icon: <em>I</em>, action: () => editor.chain().focus().toggleItalic().run(), active: editor.isActive('italic'), title: 'Itálico' },
    { icon: <s>S</s>, action: () => editor.chain().focus().toggleStrike().run(), active: editor.isActive('strike'), title: 'Tachado' },
    { divider: true },
    { icon: '•', action: () => editor.chain().focus().toggleBulletList().run(), active: editor.isActive('bulletList'), title: 'Lista' },
    { icon: '1.', action: () => editor.chain().focus().toggleOrderedList().run(), active: editor.isActive('orderedList'), title: 'Lista Numerada' },
    { divider: true },
    { icon: '🔗', action: setLink, active: editor.isActive('link'), title: 'Link' },
    { icon: '🖼️', action: addImage, title: 'Inserir Imagem' },
    { icon: '⬅', action: () => { 
      const { selection } = editor.state;
      const node = selection.$from.nodeAfter;
      if (node && node.type.name === 'image') {
        editor.chain().focus().updateAttributes('image', { align: 'left', width: '45%' }).run();
      }
    }, title: 'Imagem à Esquerda' },
    { icon: '➡', action: () => { 
      const { selection } = editor.state;
      const node = selection.$from.nodeAfter;
      if (node && node.type.name === 'image') {
        editor.chain().focus().updateAttributes('image', { align: 'right', width: '45%' }).run();
      }
    }, title: 'Imagem à Direita' },
    { divider: true },
    { icon: '─', action: () => editor.chain().focus().setHorizontalRule().run(), title: 'Linha Horizontal' },
  ];

  return (
    <div className="rich-editor">
      {editable && (
        <div className="rich-editor__toolbar">
          {toolbarButtons.map((btn, i) => 
            btn.divider ? (
              <span key={i} className="rich-editor__divider" />
            ) : (
              <button
                key={i}
                type="button"
                onClick={btn.action}
                className={`rich-editor__btn ${btn.active ? 'rich-editor__btn--active' : ''}`}
                title={btn.title}
              >
                {btn.icon}
              </button>
            )
          )}
        </div>
      )}
      
      <div 
        className="rich-editor__content"
        onPaste={handlePaste}
        onDrop={handleDrop}
        onDragOver={(e) => e.preventDefault()}
      >
        <EditorContent editor={editor} />
      </div>
      
      {editable && (
        <div className="rich-editor__footer">
          {error && (
            <div className="rich-editor__error">{error}</div>
          )}
          {uploading && (
            <div className="rich-editor__uploading">
              <span className="rich-editor__spinner" /> Enviando imagem...
            </div>
          )}
          <div className="rich-editor__info">
            <span className="rich-editor__types" title="Formatos aceitos">
              PNG, JPEG, WebP, GIF
            </span>
            <span className="rich-editor__limits">
              {imageCount}/{MAX_IMAGES} imagens
            </span>
          </div>
        </div>
      )}
    </div>
  );
}