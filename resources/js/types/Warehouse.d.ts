export interface Warehouse {
    id: number;
    region_id: number;
    reference: string;
    name: string;
    address: string;
    phone: string;
    manager: string;
    trashed?: boolean;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}