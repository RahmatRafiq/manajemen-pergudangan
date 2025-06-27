import { type User, type NavItem } from '@/types';
import { hasRole, hasAnyRole, hasPermission, isAdmin } from './permissions';

/**
 * Check if user can access a nav item
 */
export function canAccessNavItem(user: User, navItem: NavItem): boolean {
    // If no restrictions, allow access
    if (!navItem.adminOnly && !navItem.requiresRole && !navItem.requiresPermission) {
        return true;
    }
    
    // Check admin only access
    if (navItem.adminOnly && !isAdmin(user)) {
        return false;
    }
    
    // Check role requirements
    if (navItem.requiresRole) {
        if (Array.isArray(navItem.requiresRole)) {
            if (!hasAnyRole(user, navItem.requiresRole)) {
                return false;
            }
        } else {
            if (!hasRole(user, navItem.requiresRole)) {
                return false;
            }
        }
    }
    
    // Check permission requirements
    if (navItem.requiresPermission) {
        if (Array.isArray(navItem.requiresPermission)) {
            // For multiple permissions, user must have at least one
            const hasAnyPermission = navItem.requiresPermission.some(permission => 
                hasPermission(user, permission)
            );
            if (!hasAnyPermission) {
                return false;
            }
        } else {
            if (!hasPermission(user, navItem.requiresPermission)) {
                return false;
            }
        }
    }
    
    return true;
}

/**
 * Filter nav items based on user permissions
 */
export function filterNavItems(user: User, navItems: NavItem[]): NavItem[] {
    return navItems
        .filter(item => canAccessNavItem(user, item))
        .map(item => {
            // If item has children, filter them recursively
            if (item.children && item.children.length > 0) {
                const filteredChildren = filterNavItems(user, item.children);
                
                // If no children remain after filtering, hide the parent item
                if (filteredChildren.length === 0) {
                    return null;
                }
                
                return {
                    ...item,
                    children: filteredChildren
                };
            }
            
            return item;
        })
        .filter((item): item is NavItem => item !== null);
}
