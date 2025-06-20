export interface Category {
    id: number;
    name: string;
    type: string;
    description: string;
    trashed?: boolean;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}