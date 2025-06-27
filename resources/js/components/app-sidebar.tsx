import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { filterNavItems } from '@/utils/nav-filter';
import { Link, usePage } from '@inertiajs/react';
import { 
    Shield, 
    Key, 
    Users, 
    Home,
    AlertTriangle,
    UserCog,
    FolderOpen,
    ShoppingCart,
    Warehouse,
    BarChart3,
    ArrowRightLeft,
    Github,
    FileText
} from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: Home,
    },
    {
        title: 'Inventory Alerts',
        href: '/stock-alerts',
        icon: AlertTriangle,
    },
    // {
    //     title: 'Log Activity',
    //     href: '/activity-logs',
    //     icon: Activity,
    // },
    {
        title: 'Users Management',
        href: '',
        icon: UserCog,
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
                icon: Users,
                adminOnly: true,
            },
        ],
    },
    {
        title: 'Category Management',
        href: '/category',
        icon: FolderOpen,
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
        icon: ShoppingCart,
        adminOnly: true, // Only admin can access
    },
    {
        title: 'Inventory Management',
        href: '/inventory',
        icon: Warehouse,
    },
    {
        title: 'Movement Analysis',
        href: '/inventory/sorted/global',
        icon: BarChart3,
    },
    {
        title: 'Inventory Transactions',
        href: '/stock-transaction',
        icon: ArrowRightLeft,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Github,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: FileText,
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
