import { type ReactNode } from 'react';
import { useAuth } from '@/hooks/use-auth';

interface ProtectedComponentProps {
    children: ReactNode;
    fallback?: ReactNode;
    adminOnly?: boolean;
    requireRole?: string | string[];
    requirePermission?: string | string[];
    requireAll?: boolean; // For multiple roles/permissions, require all instead of any
}

/**
 * Component to protect content based on user roles and permissions
 */
export function ProtectedComponent({
    children,
    fallback = null,
    adminOnly = false,
    requireRole,
    requirePermission,
    requireAll = false
}: ProtectedComponentProps) {
    const { user, isAdmin, hasRole, hasAnyRole, hasAllRoles, hasPermission } = useAuth();
    
    // If no user, deny access
    if (!user) {
        return <>{fallback}</>;
    }
    
    // Check admin only access
    if (adminOnly && !isAdmin()) {
        return <>{fallback}</>;
    }
    
    // Check role requirements
    if (requireRole) {
        let hasRequiredRole = false;
        
        if (Array.isArray(requireRole)) {
            hasRequiredRole = requireAll 
                ? hasAllRoles(requireRole)
                : hasAnyRole(requireRole);
        } else {
            hasRequiredRole = hasRole(requireRole);
        }
        
        if (!hasRequiredRole) {
            return <>{fallback}</>;
        }
    }
    
    // Check permission requirements
    if (requirePermission) {
        let hasRequiredPermission = false;
        
        if (Array.isArray(requirePermission)) {
            if (requireAll) {
                hasRequiredPermission = requirePermission.every(permission => 
                    hasPermission(permission)
                );
            } else {
                hasRequiredPermission = requirePermission.some(permission => 
                    hasPermission(permission)
                );
            }
        } else {
            hasRequiredPermission = hasPermission(requirePermission);
        }
        
        if (!hasRequiredPermission) {
            return <>{fallback}</>;
        }
    }
    
    return <>{children}</>;
}

/**
 * HOC to protect components
 */
export function withRoleProtection<P extends object>(
    Component: React.ComponentType<P>,
    protectionProps: Omit<ProtectedComponentProps, 'children' | 'fallback'>
) {
    return function ProtectedComponentWrapper(props: P & { fallback?: ReactNode }) {
        const { fallback, ...componentProps } = props;
        
        return (
            <ProtectedComponent {...protectionProps} fallback={fallback}>
                <Component {...(componentProps as P)} />
            </ProtectedComponent>
        );
    };
}

/**
 * Convenience components for common use cases
 */
export const AdminOnly = ({ children, fallback }: { children: ReactNode; fallback?: ReactNode }) => (
    <ProtectedComponent adminOnly fallback={fallback}>
        {children}
    </ProtectedComponent>
);

export const UserOnly = ({ children, fallback }: { children: ReactNode; fallback?: ReactNode }) => (
    <ProtectedComponent requireRole="user" fallback={fallback}>
        {children}
    </ProtectedComponent>
);

export const AuthenticatedOnly = ({ children, fallback }: { children: ReactNode; fallback?: ReactNode }) => {
    const { isAuthenticated } = useAuth();
    
    if (!isAuthenticated) {
        return <>{fallback}</>;
    }
    
    return <>{children}</>;
};
