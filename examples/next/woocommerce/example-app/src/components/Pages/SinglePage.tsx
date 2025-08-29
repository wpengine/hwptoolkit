import React from "react";
import { Page } from "@/interfaces/page.interface";
import styles from "./SinglePage.module.scss";

interface pageResponse {
	page: Page;
}

export default function SinglePage({ page }: pageResponse) {
	if (!page) {
		return null;
	}

	if (page) {
		return (
			<div className="container mx-auto py-8">
				<article className="">
					<h1 className={styles.title}>{page.title}</h1>
					{page.content && <div dangerouslySetInnerHTML={{ __html: page.content }} />}
					{page.commentsCount && (
						<section className="mt-12 pt-8 border-t border-gray-200">
							<h3 className="text-2xl font-bold text-gray-900 mb-6">Comments</h3>
							<div className="bg-gray-50 rounded-lg p-6 text-center">
								<p className="text-sm text-gray-500 mt-2">Comment count: {page.commentsCount || 0}</p>
							</div>
						</section>
					)}
				</article>
			</div>
		);
	}
}
