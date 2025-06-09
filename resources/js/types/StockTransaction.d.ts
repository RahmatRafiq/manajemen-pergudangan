export interface StockTransaction {
    id: number;
    inventory_id: number;
    type: StockTransactionType;
    quantity: number;
    reference?: string;
    description?: string;
    created_by?: string | number;
    approved_by?: string | number;
    created_at: string;
    approved_at?: string | null;
    trashed?: boolean;
    inventory?: {
        id: number;
        warehouse: { id: number; name: string };
        product: { id: number; name: string; sku: string };
    };
}