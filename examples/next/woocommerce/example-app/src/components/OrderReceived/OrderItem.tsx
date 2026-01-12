import { LineItem } from "@/interfaces/order.interface";
import Link from "next/dist/client/link";
import Image from "next/image";

const convertToPrice = (number: string | number) => {
	const num = typeof number === "string" ? parseFloat(number) : number;
	return `$${num.toFixed(2)}`;
};

export default function OrderItem({ item }: { item: LineItem }) {
	const hasImage = item.product?.node?.image?.sourceUrl;
	const productName = item.product?.node?.name || `Product ${item.databaseId}`;
	return (
		<div className="flex gap-4 items-center py-3 border-b border-gray-100 last:border-b-0">
			{hasImage && (
				<div className="relative w-20 h-20 flex-shrink-0 bg-gray-100 rounded-md overflow-hidden">
					<Link href={`/product/${item.product.node.slug}`}>
						<Image
							src={item.product.node.image.sourceUrl}
							alt={item.product.node.image.altText || productName}
							fill
							className="object-cover"
							sizes="80px"
							loading="lazy"
						/>
					</Link>
				</div>
			)}
			<div className="flex-1 min-w-0">
				<p className="font-medium text-gray-900 truncate">{productName}</p>
				<p className="text-sm text-gray-600">Quantity: {item.quantity}</p>
				{item.subtotal && item.subtotal !== item.total && (
					<p className="text-xs text-gray-500">
						Price: {item.subtotal} × {item.quantity}
					</p>
				)}
			</div>
			<div className="text-right flex-shrink-0">
				<p className="font-semibold text-gray-900">{convertToPrice(item.total)}</p>
			</div>
		</div>
	);
}
