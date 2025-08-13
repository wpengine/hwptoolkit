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
    const baseClasses = ['nav-link'];
    if (isActive) baseClasses.push('active');
    if (cssClasses) baseClasses.push(...cssClasses);
    return baseClasses.join(' ');
  };

  const getDropdownItemClasses = (cssClasses) => {
    const baseClasses = ['dropdown-item'];
    if (isActive) baseClasses.push('active');
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
        {item.label}
        {/* Add dropdown indicator if there are children */}
        {hasChildren && (
          <span className="dropdown-arrow">
            {level === 0 ? "▼" : "▶"}
          </span>
        )}
      </Link>

      {/* Dropdown menu for children, if any */}
      {hasChildren && (
        <div className={`${dropdownClass} dropdown`}>
          {item.children.map((child) => (
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
                  {child.label}
                </Link>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}