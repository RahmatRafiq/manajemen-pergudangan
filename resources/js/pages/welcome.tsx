import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Trans Mamminasata - Sistem Manajemen Pergudangan">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
                <header className="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
                    <nav className="flex items-center justify-end gap-4">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={route('login')}
                                    className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                </header>
                <div className="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
                    <main className="flex w-full max-w-[335px] flex-col-reverse lg:max-w-4xl lg:flex-row">
                        <div className="flex-1 rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-tl-lg lg:rounded-br-none lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                            <h1 className="mb-1 font-medium">Selamat Datang di Trans Mamminasata</h1>
                            <p className="mb-2 text-[#706f6c] dark:text-[#A1A09A]">
                                Sistem Manajemen Pergudangan Terpadu.
                                <br />
                                Mulai dengan fitur-fitur berikut ini.
                            </p>
                            <ul className="mb-4 flex flex-col lg:mb-6">
                                <li className="relative flex items-center gap-4 py-2">
                                    <span className="relative bg-white py-1 dark:bg-[#161615]">
                                        <span className="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                                            <span className="h-1.5 w-1.5 rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A]" />
                                        </span>
                                    </span>
                                    <span>
                                        Kelola inventori dengan mudah dan efisien
                                    </span>
                                </li>
                                <li className="relative flex items-center gap-4 py-2">
                                    <span className="relative bg-white py-1 dark:bg-[#161615]">
                                        <span className="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                                            <span className="h-1.5 w-1.5 rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A]" />
                                        </span>
                                    </span>
                                    <span>
                                        Pantau stok real-time dengan alert otomatis
                                    </span>
                                </li>
                                <li className="relative flex items-center gap-4 py-2">
                                    <span className="relative bg-white py-1 dark:bg-[#161615]">
                                        <span className="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                                            <span className="h-1.5 w-1.5 rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A]" />
                                        </span>
                                    </span>
                                    <span>
                                        Analisis pergerakan barang dan laporan
                                    </span>
                                </li>
                            </ul>
                            {!auth.user && (
                                <div className="flex gap-4 mt-6">
                                    <Link
                                        href={route('register')}
                                        className="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                                    >
                                        Mulai Sekarang
                                    </Link>
                                    <Link
                                        href={route('login')}
                                        className="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                                    >
                                        Login
                                    </Link>
                                </div>
                            )}
                        </div>
                        <div className="relative -mb-px aspect-[335/376] w-full shrink-0 overflow-hidden rounded-t-lg bg-gradient-to-br from-blue-50 to-indigo-100 lg:mb-0 lg:-ml-px lg:aspect-auto lg:w-[438px] lg:rounded-t-none lg:rounded-r-lg dark:from-gray-800 dark:to-gray-900">
                            <div className="flex flex-col items-center justify-center h-full p-8">
                                <div className="mb-8 transform transition-all duration-750 opacity-100 translate-y-0 starting:translate-y-6 starting:opacity-0">
                                    <img 
                                        src="/logo.png" 
                                        alt="Trans Mamminasata Logo" 
                                        className="w-32 h-32 lg:w-40 lg:h-40 object-cover rounded-2xl shadow-2xl border-4 border-white dark:border-gray-700"
                                    />
                                </div>
                                <div className="text-center transform transition-all duration-750 delay-300 opacity-100 translate-y-0 starting:translate-y-4 starting:opacity-0">
                                    <h2 className="text-3xl lg:text-4xl font-bold text-gray-800 dark:text-white mb-3">
                                        Trans Mamminasata
                                    </h2>
                                    <p className="text-lg text-gray-600 dark:text-gray-300 mb-2">
                                        Sistem Manajemen Pergudangan
                                    </p>
                                    <p className="text-sm text-gray-500 dark:text-gray-400">
                                        Solusi Terpadu untuk Manajemen Inventori
                                    </p>
                                </div>
                            </div>
                            <div className="absolute inset-0 rounded-t-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-t-none lg:rounded-r-lg dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]" />
                        </div>
                    </main>
                </div>
                <div className="hidden h-14.5 lg:block"></div>
            </div>
        </>
    );
}
