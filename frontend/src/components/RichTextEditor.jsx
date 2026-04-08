import React, { useState, useCallback, useEffect } from 'react';
import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import Image from '@tiptap/extension-image';
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
  
  void topicId;

  const countImages = useCallback((html) => {
    const div = document.createElement('div');
    div.innerHTML = html;
    return div.querySelectorAll('img').length;
  }, []);

  const stripHtml = useCallback((html) => {
    const div = document.createElement('div');
    div.innerHTML = html;
    return div.textContent || div.innerText || '';
  }, []);

  const editor = useEditor({
    extensions: [
      StarterKit.configure({
        heading: false,
        codeBlock: false,
      }),
      Image.configure({
        inline: false,
        allowBase64: false,
        HTMLAttributes: {
          class: 'editor-image',
        },
      }),
      Link.configure({
        openOnClick: false,
      }),
      Placeholder.configure({
        placeholder,
      }),
    ],
    content: content,
    editable,
    editorProps: {
      attributes: {
        class: 'rich-text-editor__input',
      },
    },
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
    if (editor && content !== editor.getHTML()) {
      editor.commands.setContent(content || '');
    }
  }, [content, editor]);

  const handleImageUpload = async (file) => {
    if (!file) return;
    
    if (!ALLOWED_TYPES.includes(file.type)) {
      setError('Tipo de arquivo não permitido. Use PNG, JPG, WEBP ou GIF.');
      return;
    }
    
    if (imageCount >= MAX_IMAGES) {
      setError(`Limite de ${MAX_IMAGES} imagens excedido.`);
      return;
    }

    setUploading(true);
    setError(null);

    try {
      const response = await assetService.uploadInline(file);
      const imageUrl = response.url;

      const align = imageCount % 2 === 0 ? 'left' : 'right';
      const className = align === 'left' ? 'image-left' : 'image-right';
      
      editor.chain().focus().insertContent(`<img src="${imageUrl}" class="${className}" />`).run();
      setImageCount(prev => prev + 1);
    } catch (err) {
      const message = err.response?.data?.message || err.response?.data?.error || 'Erro ao fazer upload da imagem. Tente novamente.';
      setError(message);
      console.error('Upload error:', err);
    } finally {
      setUploading(false);
    }
  };

  const handleFileInput = (e) => {
    const file = e.target.files?.[0];
    if (file) {
      console.log('Selected file:', file.name, file.type, file.size);
      handleImageUpload(file);
    }
  };

  const handleDrop = useCallback((e) => {
    e.preventDefault();
    const file = e.dataTransfer?.files?.[0];
    if (file) handleImageUpload(file);
  }, [editor, imageCount]);

  const handleDragOver = (e) => {
    e.preventDefault();
  };

  if (!editor) {
    return <div>Carregando editor...</div>;
  }

  return (
    <div className="rich-text-editor">
      {editable && (
        <div className="rich-text-editor__toolbar">
          <button
            type="button"
            onClick={() => editor.chain().focus().toggleBold().run()}
            className={`toolbar-btn ${editor.isActive('bold') ? 'toolbar-btn--active' : ''}`}
            title="Negrito"
          >
            <b>B</b>
          </button>
          <button
            type="button"
            onClick={() => editor.chain().focus().toggleItalic().run()}
            className={`toolbar-btn ${editor.isActive('italic') ? 'toolbar-btn--active' : ''}`}
            title="Itálico"
          >
            <i>I</i>
          </button>
          <button
            type="button"
            onClick={() => editor.chain().focus().toggleStrike().run()}
            className={`toolbar-btn ${editor.isActive('strike') ? 'toolbar-btn--active' : ''}`}
            title="Tachado"
          >
            <s>S</s>
          </button>
          
          <div className="toolbar-divider" />
          
          <button
            type="button"
            onClick={() => {
              const url = window.prompt('Digite a URL do link:');
              if (url) {
                editor.chain().focus().setLink({ href: url }).run();
              }
            }}
            className={`toolbar-btn ${editor.isActive('link') ? 'toolbar-btn--active' : ''}`}
            title="Adicionar Link"
          >
            🔗
          </button>
          
          <div className="toolbar-divider" />
          
          <label className="toolbar-btn toolbar-btn--image" title="Adicionar Imagem">
            {uploading ? '⏳' : '📷'}
            <input
              type="file"
              accept={ALLOWED_TYPES.join(',')}
              onChange={handleFileInput}
              style={{ display: 'none' }}
            />
          </label>
          
          <button
            type="button"
            onClick={() => editor.chain().focus().undo().run()}
            className="toolbar-btn"
            title="Desfazer"
            disabled={!editor.can().undo()}
          >
            ↩️
          </button>
          <button
            type="button"
            onClick={() => editor.chain().focus().redo().run()}
            className="toolbar-btn"
            title="Refazer"
            disabled={!editor.can().redo()}
          >
            ↪️
          </button>
        </div>
      )}

      <div 
        className="rich-text-editor__content"
        onDrop={handleDrop}
        onDragOver={handleDragOver}
      >
        <EditorContent editor={editor} />
      </div>

      <div className="rich-text-editor__footer">
        {error && <span className="rich-text-editor__error">{error}</span>}
        <span className="rich-text-editor__counter">
          {stripHtml(editor.getHTML()).length}/{MAX_CHARS} | Imagens: {imageCount}/{MAX_IMAGES}
        </span>
      </div>
    </div>
  );
}