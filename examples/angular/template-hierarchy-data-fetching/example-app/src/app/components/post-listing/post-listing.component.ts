import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { LoadingComponent } from '../loading/loading.component';
import { Post } from '../../interfaces/post.interface';
import { formatDate, createExcerpt, getCategoryLink, getTagLink } from '../../utils/utils';

@Component({
  selector: 'app-post-listing',
  standalone: true,
  imports: [CommonModule, RouterModule, LoadingComponent],
  templateUrl: './post-listing.component.html',
  styleUrl: './post-listing.component.scss',
})
export class PostListingComponent {
  @Input() posts: Post[] = [];
  @Input() loading: boolean = false;
  @Input() cols: number = 3;

  formatDate(dateString: string): string {
    return formatDate(dateString);
  }
  createExcerpt(content: string, length: number = 100): string {
    return createExcerpt(content, length);
  }
  getCategoryLink(category: string): string {
    return getCategoryLink(category);
  }
  getTagLink(tag: string): string {
    return getTagLink(tag);
  }
  trackByPostId(index: number, post: Post): string {
    return post.id;
  }

  hasCategories(post: Post): boolean {
    return !!(post.categories?.nodes && post.categories.nodes.length > 0);
  }
  hasTags(post: Post): boolean {
    return !!(post.tags?.nodes && post.tags.nodes.length > 0);
  }

  isLastItem(index: number, array: any[]): boolean {
    return index === array.length - 1;
  }
}
