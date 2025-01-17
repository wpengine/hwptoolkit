'use client'

import { usePathname } from 'next/navigation';


export default function ArchivePage() {
    const pathname = usePathname();
    const slug = pathname.substring(pathname.lastIndexOf('/') + 1);
    return (
        <div>
            <h1 className="text-3xl font-bold mb-12 mt-24">Archive Page - {slug}</h1>
            <p className="text-lg mb-6">This is some dummy content for the archive page. Here you can list your archive posts or any other relevant information.</p>
            <ul className="list-disc pl-5 space-y-2">
                <li className="text-base">{slug} Post 1</li>
                <li className="text-base">{slug} Post 2</li>
                <li className="text-base">{slug} Post 3</li>
            </ul>
        </div>
    );
}
