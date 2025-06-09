export interface Inventory {
    id: number;
    warehouse_id: number;
    product_id: number;
    quantity: number;
    reserved: number;
    min_stock: number;
    max_stock: number;
    updated_by: number | null;
    updated_at: string;
    trashed?: boolean;
    warehouse?: {
        id: number;
        name: string;
        reference: string;
    };
    product?: {
        id: number;
        name: string;
        sku: string;
    };
}