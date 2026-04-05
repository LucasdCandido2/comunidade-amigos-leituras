import React, { useState } from 'react';

export function SpoilerTag({ children, defaultRevealed = false }) {
    const [revealed, setRevealed] = useState(defaultRevealed);

    if (revealed) {
        return <span className="spoiler-content">{children}</span>;
    }

    return (
        <span 
            className="spoiler-tag" 
            onClick={() => setRevealed(true)}
            title="Clique para revelar o spoiler"
        >
            <span className="spoiler-tag__hidden">{'\u2588'.repeat(15)}</span>
            <span className="spoiler-tag__overlay">
                ⚠️ Spoiler - clique para revelar
            </span>
        </span>
    );
}

export function SpoilerText({ content }) {
    const spoilerRegex = /\[spoiler\](.*?)\[\/spoiler\]/gs;
    const parts = [];
    let lastIndex = 0;
    let match;

    while ((match = spoilerRegex.exec(content)) !== null) {
        if (match.index > lastIndex) {
            parts.push({
                type: 'text',
                content: content.slice(lastIndex, match.index),
            });
        }
        
        parts.push({
            type: 'spoiler',
            content: match[1],
        });
        
        lastIndex = match.index + match[0].length;
    }

    if (lastIndex < content.length) {
        parts.push({
            type: 'text',
            content: content.slice(lastIndex),
        });
    }

    return (
        <>
            {parts.map((part, index) => {
                if (part.type === 'spoiler') {
                    return <SpoilerTag key={index}>{part.content}</SpoilerTag>;
                }
                return <span key={index}>{part.content}</span>;
            })}
        </>
    );
}
