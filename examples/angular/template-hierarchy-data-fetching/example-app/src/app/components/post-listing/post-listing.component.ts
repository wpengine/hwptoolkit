import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { LoadingComponent } from '../loading/loading.component';
import { Post } from '../../shared/interfaces/post.interface';

@Component({
  selector: 'app-post-listing',
  standalone: true,
  imports: [CommonModule, RouterModule, LoadingComponent],
  templateUrl: './post-listing.component.html',
  styleUrl: './post-listing.component.scss'
})
export class PostListingComponent {
  @Input() posts: Post[] = [];
  @Input() loading: boolean = false;
  @Input() cols: number = 3;

  /** 
   * Format date to readable format
   */
  formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }
  trackByPostId(index: number, post: Post): string {
    return post.id;
  }
  /**
   * Create excerpt with word limit
   */
  createExcerpt(excerpt: string, wordLimit: number = 150): string {
    if (!excerpt) return '';
    
    // Remove HTML tags
    const textOnly = excerpt.replace(/<[^>]*>/g, '');
    
    // Split into words and limit
    const words = textOnly.split(' ');
    if (words.length <= wordLimit) {
      return excerpt; // Return original if within limit
    }
    
    const truncated = words.slice(0, wordLimit).join(' ');
    return truncated + '...';
  }

  /**
   * Get category link URL
   */
  getCategoryLink(slug: string): string {
    return `/category/${slug}`;
  }

  /**
   * Get tag link URL
   */
  getTagLink(slug: string): string {
    return `/tag/${slug}`;
  }

  /**
   * Check if post has categories
   */
  hasCategories(post: Post): boolean {
    return !!(post.categories?.nodes && post.categories.nodes.length > 0);
  }

  /**
   * Check if post has tags
   */
  hasTags(post: Post): boolean {
    return !!(post.tags?.nodes && post.tags.nodes.length > 0);
  }

  /**
   * Check if it's the last item in array
   */
  isLastItem(index: number, array: any[]): boolean {
    return index === array.length - 1;
  }
}