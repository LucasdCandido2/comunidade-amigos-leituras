import React from "react";

export const withMemo = (Component, propsAreEqual) => {
  return React.memo(Component, propsAreEqual || (() => true));
};

export const createMemoizedComponent = (Component) => {
  return React.memo(Component, (prevProps, nextProps) => {
    const keys = Object.keys(nextProps);
    for (const key of keys) {
      if (prevProps[key] !== nextProps[key]) {
        return false;
      }
    }
    return true;
  });
};

export const useMemoizedValue = (value, deps = []) => {
  const ref = React.useRef({ value, deps });
  
  if (ref.current.value !== value || !deepEqual(ref.current.deps, deps)) {
    ref.current = { value, deps };
  }
  
  return ref.current.value;
};

function deepEqual(a, b) {
  if (a === b) return true;
  if (a == null || b == null) return false;
  if (typeof a !== typeof b) return false;
  
  if (Array.isArray(a) && Array.isArray(b)) {
    if (a.length !== b.length) return false;
    return a.every((item, i) => deepEqual(item, b[i]));
  }
  
  if (typeof a === 'object' && typeof b === 'object') {
    const keysA = Object.keys(a);
    const keysB = Object.keys(b);
    if (keysA.length !== keysB.length) return false;
    return keysA.every(key => deepEqual(a[key], b[key]));
  }
  
  return false;
}

export const MemoizedTopicsList = React.memo(function MemoizedTopicsList({ topics, user, onViewTopic }) {
  return null;
});

export const createPerformanceOptimizer = (Component) => {
  return React.memo(
    (props) => <Component {...props} />,
    (prevProps, nextProps) => {
      const skipKeys = ['onViewTopic', 'onBack', 'onTopicDeleted', 'onWorkSaved', 'onCancel'];
      
      for (const key of Object.keys(nextProps)) {
        if (skipKeys.includes(key)) continue;
        if (prevProps[key] !== nextProps[key]) return false;
      }
      return true;
    }
  );
};