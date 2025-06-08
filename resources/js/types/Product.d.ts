type Product = {
    id: number;
    sku: string;
    name: string;
    category: string;
    category_id?: number;
    unit: string;
    description?: string;
    created_by: string;
    trashed?: boolean;
};