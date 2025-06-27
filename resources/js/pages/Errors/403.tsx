import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ShieldAlert, ArrowLeft, Home } from 'lucide-react';

interface ErrorPageProps {
    status?: number;
    message?: string;
    required_role?: string;
    required_permission?: string;
    user_roles?: string[];
    user_permissions?: string[];
}

export default function Error403({ 
    status = 403, 
    message = "Access Denied",
    required_role,
    required_permission,
    user_roles = [],
    user_permissions = []
}: ErrorPageProps) {
    return (
        <>
            <Head title={`${status} - ${message}`} />
            
            <div className="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center px-4">
                <Card className="w-full max-w-md">
                    <CardHeader className="text-center">
                        <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                            <ShieldAlert className="h-8 w-8 text-red-600 dark:text-red-400" />
                        </div>
                        <CardTitle className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {status}
                        </CardTitle>
                        <CardDescription className="text-lg text-gray-600 dark:text-gray-400">
                            {message}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <p className="text-center text-sm text-gray-500 dark:text-gray-400">
                            You don't have permission to access this resource. Please contact your administrator if you believe this is an error.
                        </p>
                        
                        {/* Show detailed info for debugging (only in development) */}
                        {(required_role || required_permission) && (
                            <div className="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs space-y-2">
                                {required_role && (
                                    <p className="text-gray-600 dark:text-gray-400">
                                        <strong>Required Role:</strong> {required_role}
                                    </p>
                                )}
                                {required_permission && (
                                    <p className="text-gray-600 dark:text-gray-400">
                                        <strong>Required Permission:</strong> {required_permission}
                                    </p>
                                )}
                                {user_roles.length > 0 && (
                                    <p className="text-gray-600 dark:text-gray-400">
                                        <strong>Your Roles:</strong> {user_roles.join(', ')}
                                    </p>
                                )}
                                {user_permissions.length > 0 && (
                                    <p className="text-gray-600 dark:text-gray-400">
                                        <strong>Your Permissions:</strong> {user_permissions.join(', ')}
                                    </p>
                                )}
                            </div>
                        )}
                        
                        <div className="flex flex-col space-y-2">
                            <Button asChild variant="default" className="w-full">
                                <Link href="/dashboard">
                                    <Home className="mr-2 h-4 w-4" />
                                    Go to Dashboard
                                </Link>
                            </Button>
                            
                            <Button 
                                variant="outline" 
                                className="w-full"
                                onClick={() => window.history.back()}
                            >
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Go Back
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
