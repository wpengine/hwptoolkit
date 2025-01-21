'use client'

import { usePathname } from 'next/navigation';


export default function SingluarPage() {
    const pathname = usePathname();
    const slug = pathname.substring(pathname.lastIndexOf('/') + 1);
    return (
        <div>
            <h1 className="text-3xl font-bold mb-12 mt-24">Single Page - {slug}</h1>
            <p className="text-lg mb-6">This is some dummy content for the single page.</p>
        </div>
    );
}
