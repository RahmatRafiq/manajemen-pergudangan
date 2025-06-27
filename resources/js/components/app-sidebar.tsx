import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { filterNavItems } from '@/utils/nav-filter';
import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Folder, LayoutGrid, Users, Shield, Key, User, Package, Bell, TrendingDown } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Stock Alerts',
        href: '/stock-alerts',
        icon: Bell,
    },
    // {
    //     title: 'Log Activity',
    //     href: '/activity-logs',
    //     icon: Activity,
    // },
    {
        title: 'Users Management',
        href: '',
        icon: Users,
        adminOnly: true, // Only admin can access
        children: [
            {
                title: 'Roles',
                href: '/roles',
                icon: Shield,
                adminOnly: true,
            },
            {
                title: 'Permissions',
                href: '/permissions',
                icon: Key,
                adminOnly: true,
            },
            {
                title: 'User',
                href: '/users',
                icon: User,
                adminOnly: true,
            },
        ],
    },
    {
        title: 'Category Management',
        href: '/category',
        icon: Folder,
        adminOnly: true, // Only admin can access
    },
    // {
    //     title: 'Warehouse Management',
    //     href: '/warehouse',
    //     icon: LayoutGrid,
    // },
    {
        title: 'Products Management',
        href: '/product',
        icon: Package,
        adminOnly: true, // Only admin can access
    },
    {
        title: 'Inventory Management',
        href: '/inventory',
        icon: Folder,
    },
    {
        title: 'Analisis Pergerakan',
        href: '/inventory/sorted/global',
        icon: TrendingDown,
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
    const { auth } = usePage<SharedData>().props;
    const filteredMainNavItems = filterNavItems(auth.user, mainNavItems);
    
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
                <NavMain items={filteredMainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
