import { CinemaListingsQuery } from "@/lib/queries/CinemaListingsQuery";
import { CustomPostTypeTemplate } from "@/components/cpt/CustomPostTypeTemplate";

export default async function CinemaListingsPage(params) {
  return CustomPostTypeTemplate(CinemaListingsQuery, {
    params: params,
    customPostType: "movies",
    siteKey: "movie_site",
    title: "Cinema Listings",
    cacheExpiry: 3600,
  });
}
