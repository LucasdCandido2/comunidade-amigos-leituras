import React, { useState } from 'react';
import { sanitizeHtml } from '../utils/sanitize';

function processSpoilerContent(content, revealedState = {}, onToggle = null) {
    const spoilerRegex = /\[spoiler\](.*?)\[\/spoiler\]/gs;
    const parts = [];
    let match;
    let index = 0;
    
    while ((match = spoilerRegex.exec(content)) !== null) {
        parts.push({
            type: 'spoiler',
            index: index,
            content: match[1]
        });
        index++;
    }
    
    if (parts.length === 0) {
        return <span dangerouslySetInnerHTML={{ __html: sanitizeHtml(content) }} />;
    }
    
    let lastIndex = 0;
    const elements = [];
    
    parts.forEach((part) => {
        const textBeforeStart = content.indexOf('[spoiler]', lastIndex);
        if (textBeforeStart > lastIndex) {
            const textBefore = content.slice(lastIndex, textBeforeStart);
            elements.push(
                <span key={`text-${part.index}`} dangerouslySetInnerHTML={{ __html: sanitizeHtml(textBefore) }} />
            );
        }
        
        const isRevealed = revealedState[part.index];
        
        if (onToggle) {
            elements.push(
                <span 
                    key={`spoiler-${part.index}`}
                    className="spoiler-tag"
                    onClick={() => onToggle(part.index)}
                    data-spoiler={isRevealed ? 'revealed' : 'hidden'}
                >
                    {isRevealed ? part.content : '⚠️ Spoiler'}
                </span>
            );
        } else {
            elements.push(
                <span 
                    key={`spoiler-${part.index}`}
                    className="spoiler-tag"
                    data-spoiler="hidden"
                >
                    ⚠️ Spoiler
                </span>
            );
        }
        
        lastIndex = content.indexOf('[/spoiler]', lastIndex) + '[/spoiler]'.length;
    });
    
    if (lastIndex < content.length) {
        elements.push(
            <span key="text-end" dangerouslySetInnerHTML={{ __html: sanitizeHtml(content.slice(lastIndex)) }} />
        );
    }
    
    return <span>{elements}</span>;
}

export function SpoilerRenderer({ content, interactive = false }) {
    const [revealedSpoilers, setRevealedSpoilers] = useState({});
    
    const toggleSpoiler = (index) => {
        setRevealedSpoilers(prev => ({
            ...prev,
            [index]: !prev[index]
        }));
    };
    
    return processSpoilerContent(content, revealedSpoilers, interactive ? toggleSpoiler : null);
}

export default SpoilerRenderer;
