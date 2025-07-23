import { Component, Input, Output, EventEmitter, computed } from '@angular/core';
import { CommonModule } from '@angular/common';

export interface CommentAuthor {
  node: {
    name: string;
    url?: string;
    avatar?: {
      url: string;
    };
  };
}

export interface Comment {
  id: string;
  content: string;
  date: string;
  author: CommentAuthor;
  parentId?: string;
}

@Component({
  selector: 'app-comment-item',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './comment-item.component.html',
  styleUrl: './comment-item.component.scss'
})
export class CommentItemComponent {
  @Input() comment!: Comment;
  @Output() reply = new EventEmitter<Comment>();

  // Computed properties using Angular signals
  avatarUrl = computed(() => {
    return (
      this.comment?.author?.node?.avatar?.url ||
      `https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y`
    );
  });

  authorName = computed(() => {
    return this.comment?.author?.node?.name || 'Anonymous';
  });

  authorUrl = computed(() => {
    return this.comment?.author?.node?.url;
  });

  /**
   * Format date for display
   */
  formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  /**
   * Handle reply button click
   */
  onReply(): void {
    this.reply.emit(this.comment);
  }
}