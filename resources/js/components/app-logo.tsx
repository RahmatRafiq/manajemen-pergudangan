export default function AppLogo() {
    return (
        <div className="flex items-center space-x-3">
            <div className="flex aspect-square size-8 items-center justify-center rounded-md overflow-hidden">
                <img 
                    src="/logo.png" 
                    alt="Logo" 
                    className="size-8 object-cover rounded-md"
                />
            </div>
            <div className="flex-1 text-left">
                <span className="text-sm font-semibold whitespace-nowrap">Trans Mamminasata</span>
            </div>
        </div>
    );
}
