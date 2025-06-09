import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, Folder, LayoutGrid, Users, Shield, Activity, Key, User, Package } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Log Activity',
        href: '/activity-logs',
        icon: Activity,
    },
    {
        title: 'Users Management',
        href: '',
        icon: Users,
        children: [
            {
                title: 'Roles',
                href: '/roles',
                icon: Shield,
            },
            {
                title: 'Permissions',
                href: '/permissions',
                icon: Key,
            },
            {
                title: 'User',
                href: '/users',
                icon: User,
            },
        ],
    },
    {
        title: 'Category Management',
        href: '/category',
        icon: Folder,
    },
    {
        title: 'Warehouse Management',
        href: '/warehouse',
        icon: LayoutGrid,
    },
    {
        title: 'Products Management',
        href: '/product',
        icon: Package,
    },
    {
        title: 'Inventory Management',
        href: '/inventory',
        icon: Folder,
    },
    {
        title: 'Stock Transactions',
        href: '/stock-transaction',
        icon: BookOpen,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
