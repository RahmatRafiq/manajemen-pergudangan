import { type User, type Role, type Permission } from '@/types';

/**
 * Check if user has a specific role
 */
export function hasRole(user: User, role: string): boolean {
    if (!user || !user.roles) return false;
    
    // If roles is an array of role objects
    if (Array.isArray(user.roles)) {
        return user.roles.some((userRole: Role) => userRole.name === role);
    }
    
    // If roles is a string (single role)
    if (typeof user.roles === 'string') {
        return user.roles === role;
    }
    
    return false;
}

/**
 * Check if user has any of the specified roles
 */
export function hasAnyRole(user: User, roles: string[]): boolean {
    return roles.some(role => hasRole(user, role));
}

/**
 * Check if user has all of the specified roles
 */
export function hasAllRoles(user: User, roles: string[]): boolean {
    return roles.every(role => hasRole(user, role));
}

/**
 * Check if user has a specific permission
 */
export function hasPermission(user: User, permission: string): boolean {
    if (!user || !user.permissions) return false;
    
    // If permissions is an array of permission objects
    if (Array.isArray(user.permissions)) {
        return user.permissions.some((userPermission: Permission) => userPermission.name === permission);
    }
    
    // If permissions is a string (single permission)
    if (typeof user.permissions === 'string') {
        return user.permissions === permission;
    }
    
    return false;
}

/**
 * Check if user is admin
 */
export function isAdmin(user: User): boolean {
    return hasRole(user, 'admin');
}

/**
 * Check if user is regular user
 */
export function isUser(user: User): boolean {
    return hasRole(user, 'user');
}

/**
 * Get user's role names
 */
export function getUserRoles(user: User): string[] {
    if (!user || !user.roles) return [];
    
    if (Array.isArray(user.roles)) {
        return user.roles.map((role: Role) => role.name);
    }
    
    if (typeof user.roles === 'string') {
        return [user.roles];
    }
    
    return [];
}

/**
 * Get user's permission names
 */
export function getUserPermissions(user: User): string[] {
    if (!user || !user.permissions) return [];
    
    if (Array.isArray(user.permissions)) {
        return user.permissions.map((permission: Permission) => permission.name);
    }
    
    if (typeof user.permissions === 'string') {
        return [user.permissions];
    }
    
    return [];
}
