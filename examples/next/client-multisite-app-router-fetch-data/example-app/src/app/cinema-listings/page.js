import { CinemaListingsQuery } from "@/lib/queries/CinemaListingsQuery";
import { CustomPostTypeTemplate } from "@/components/cpt/CustomPostTypeTemplate";

export default async function CinemaListingsPage(params) {
  return CustomPostTypeTemplate(CinemaListingsQuery, "movie_site", "movies", "Cinema Listings");
}
