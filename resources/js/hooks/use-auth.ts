import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { hasRole, hasAnyRole, hasAllRoles, hasPermission, isAdmin, isUser, getUserRoles, getUserPermissions } from '@/utils/permissions';

/**
 * Hook for role and permission management
 */
export function useAuth() {
    const { auth } = usePage<SharedData>().props;
    const user = auth.user;

    return {
        user,
        isAuthenticated: !!user,
        
        // Role checks
        hasRole: (role: string) => user ? hasRole(user, role) : false,
        hasAnyRole: (roles: string[]) => user ? hasAnyRole(user, roles) : false,
        hasAllRoles: (roles: string[]) => user ? hasAllRoles(user, roles) : false,
        isAdmin: () => user ? isAdmin(user) : false,
        isUser: () => user ? isUser(user) : false,
        
        // Permission checks
        hasPermission: (permission: string) => user ? hasPermission(user, permission) : false,
        
        // Get user data
        getUserRoles: () => user ? getUserRoles(user) : [],
        getUserPermissions: () => user ? getUserPermissions(user) : [],
        
        // Convenience methods
        canManageUsers: () => user ? isAdmin(user) : false,
        canManageCategories: () => user ? isAdmin(user) : false,
        canManageProducts: () => user ? isAdmin(user) : false,
        canViewInventory: () => user ? (isAdmin(user) || isUser(user)) : false,
        canManageTransactions: () => user ? (isAdmin(user) || isUser(user)) : false,
    };
}

/**
 * Hook for checking specific permissions with loading state
 */
export function usePermissionCheck(permission: string | string[], requireAll = false) {
    const { user } = useAuth();
    
    if (!user) {
        return { hasPermission: false, isLoading: false };
    }
    
    let hasPermissionResult = false;
    
    if (Array.isArray(permission)) {
        if (requireAll) {
            hasPermissionResult = permission.every(p => hasPermission(user, p));
        } else {
            hasPermissionResult = permission.some(p => hasPermission(user, p));
        }
    } else {
        hasPermissionResult = hasPermission(user, permission);
    }
    
    return {
        hasPermission: hasPermissionResult,
        isLoading: false
    };
}

/**
 * Hook for checking roles
 */
export function useRoleCheck(role: string | string[], requireAll = false) {
    const { user } = useAuth();
    
    if (!user) {
        return { hasRole: false, isLoading: false };
    }
    
    let hasRoleResult = false;
    
    if (Array.isArray(role)) {
        if (requireAll) {
            hasRoleResult = hasAllRoles(user, role);
        } else {
            hasRoleResult = hasAnyRole(user, role);
        }
    } else {
        hasRoleResult = hasRole(user, role);
    }
    
    return {
        hasRole: hasRoleResult,
        isLoading: false
    };
}
