import Link from 'next/link';
import { useMemo } from 'react';

export default function NavigationItem({ 
  item, 
  isActive = false, 
  level = 0 
}) {
  
  // Computed equivalents
  const hasChildren = useMemo(() => {
    return item.children && item.children.length > 0;
  }, [item.children]);

  const dropdownClass = useMemo(() => {
    return level === 0 ? "dropdown-top" : "dropdown-submenu";
  }, [level]);

  // Handle CSS classes array
  const getLinkClasses = (cssClasses) => {
    const baseClasses = [
      'nav-link',
      'relative',
      'px-4',
      'py-2',
      'text-gray-700',
      'hover:text-blue-600',
      'transition-colors',
      'duration-200',
      'flex',
      'items-center',
      'gap-2',
      'font-medium'
    ];
    
    if (isActive) {
      baseClasses.push('text-blue-600', 'bg-blue-50');
    }
    
    if (hasChildren) {
      baseClasses.push('group-hover:text-blue-600');
    }
    
    if (cssClasses) baseClasses.push(...cssClasses);
    return baseClasses.join(' ');
  };

  const getDropdownItemClasses = (cssClasses) => {
    const baseClasses = [
      'dropdown-item',
      'block',
      'px-4',
      'py-3',
      'text-sm',
      'text-gray-700',
      'hover:bg-gray-100',
      'hover:text-blue-600',
      'transition-colors',
      'duration-150',
      'border-b',
      'border-gray-100',
      'last:border-b-0'
    ];
    
    if (isActive) {
      baseClasses.push('bg-blue-50', 'text-blue-600', 'font-medium');
    }
    
    if (cssClasses) baseClasses.push(...cssClasses);
    return baseClasses.join(' ');
  };

  return (
    <div className="group relative">
      {/* Regular menu item */}
      <Link
        href={item.uri || '#'}
        className={getLinkClasses(item.cssClasses)}
        target={item.target || '_self'}
        title={item.title || ''}
      >
        <span>{item.label}</span>
        
        {/* Add dropdown indicator if there are children */}
        {hasChildren && (
          <span className="dropdown-arrow transition-transform duration-200 group-hover:scale-110">
            {level === 0 ? (
              <svg className="w-4 h-4 text-gray-500 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
              </svg>
            ) : (
              <svg className="w-4 h-4 text-gray-500 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            )}
          </span>
        )}
      </Link>

      {/* Dropdown menu for children, if any */}
      {hasChildren && (
        <div className={`
          ${dropdownClass} 
          dropdown
          absolute
          ${level === 0 ? 'top-full left-0' : 'top-0 left-full'}
          min-w-[200px]
          bg-white
          shadow-lg
          border
          border-gray-200
          rounded-md
          opacity-0
          invisible
          group-hover:opacity-100
          group-hover:visible
          transition-all
          duration-200
          transform
          ${level === 0 ? 'translate-y-2 group-hover:translate-y-0' : 'translate-x-2 group-hover:translate-x-0'}
          z-50
        `}>
          
          {/* Dropdown arrow */}
          <div className={`
            absolute
            ${level === 0 ? 'top-0 left-4 -translate-y-1' : 'left-0 top-4 -translate-x-1'}
            w-2
            h-2
            bg-white
            border-l
            border-t
            border-gray-200
            transform
            rotate-45
          `}></div>

          <div className="py-2">
            {item.children.map((child, index) => (
              <div key={child.id} className="dropdown-item-container">
                {/* Recursive case: if the child has children, render another NavigationItem */}
                {child.children && child.children.length > 0 ? (
                  <NavigationItem
                    item={child}
                    isActive={isActive}
                    level={level + 1}
                    target={item.target || '_self'}
                  />
                ) : (
                  /* Base case: if the child has no children, render a simple link */
                  <Link
                    href={child.uri || '#'}
                    className={getDropdownItemClasses(child.cssClasses)}
                    target={child.target || '_self'}
                    title={child.title || ''}
                  >
                    <span className="flex items-center gap-2">
                      {child.label}
                      
                      {/* Optional icon for dropdown items */}
                      {child.icon && (
                        <span className="text-gray-400">
                          {child.icon}
                        </span>
                      )}
                    </span>
                  </Link>
                )}
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}