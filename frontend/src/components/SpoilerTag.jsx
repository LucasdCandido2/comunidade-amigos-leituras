import React, { useState, useMemo } from 'react';

export function SpoilerTag({ children, defaultRevealed = false }) {
  const [revealed, setRevealed] = useState(defaultRevealed);

  if (revealed) {
    return <span className="spoiler-content" data-spoiler="revealed">{children}</span>;
  }

  return (
    <span 
      className="spoiler-tag" 
      onClick={() => setRevealed(true)}
      title="Clique para revelar o spoiler"
      role="button"
      tabIndex={0}
      onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') setRevealed(true); }}
    >
      <span className="spoiler-tag__hidden">██████████████</span>
      <span className="spoiler-tag__overlay">
        ⚠️ Spoiler - clique para revelar
      </span>
    </span>
  );
}

export function processSpoilerContent(content) {
  if (!content) return [];
  
  const spoilerRegex = /\[spoiler\](.*?)\[\/spoiler\]/gs;
  const parts = [];
  let lastIndex = 0;
  let match;
  let spoilerIndex = 0;

  while ((match = spoilerRegex.exec(content)) !== null) {
    if (match.index > lastIndex) {
      parts.push({
        type: 'text',
        content: content.slice(lastIndex, match.index),
      });
    }
    
    const spoilerId = `spoiler-render-${spoilerIndex}`;
    parts.push({
      type: 'spoiler',
      id: spoilerId,
      content: match[1],
    });
    
    spoilerIndex++;
    lastIndex = match.index + match[0].length;
  }
  
  if (lastIndex < content.length) {
    parts.push({
      type: 'text',
      content: content.slice(lastIndex),
    });
  }
  
  return parts;
}

export function SpoilerContent({ content }) {
  const [revealed, setRevealed] = useState({});
  
  const parts = useMemo(() => processSpoilerContent(content), [content]);
  
  const toggleSpoiler = (id) => {
    setRevealed(prev => ({ ...prev, [id]: !prev[id] }));
  };

  return (
    <>
      {parts.map((part, index) => {
        if (part.type === 'spoiler') {
          const isRevealed = revealed[part.id];
          if (isRevealed) {
            return <span key={index} className="spoiler-content" data-spoiler="revealed">{part.content}</span>;
          }
          return (
            <span 
              key={index} 
              className="spoiler-tag" 
              onClick={() => toggleSpoiler(part.id)}
              title="Clique para revelar o spoiler"
              role="button"
              tabIndex={0}
              onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') toggleSpoiler(part.id); }}
            >
              <span className="spoiler-tag__hidden">██████████████</span>
              <span className="spoiler-tag__overlay">
                ⚠️ Spoiler - clique para revelar
              </span>
            </span>
          );
        }
        return <span key={index}>{part.content}</span>;
      })}
    </>
  );
}

export function processSpoilerTags(html) {
  return html;
}