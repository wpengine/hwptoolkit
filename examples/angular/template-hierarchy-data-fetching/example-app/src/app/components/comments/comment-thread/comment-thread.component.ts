import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';

export interface Comment {
  id: string;
  content: string;
  author: {
    node: {
      name: string;
      url?: string;
      avatar?: {
        url: string;
      };
    };
  };
  date: string;
  parentId?: string;
  replies?: Comment[];
}

@Component({
  selector: 'app-comment-thread',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './comment-thread.component.html',
  styleUrls: ['./comment-thread.component.scss']
})
export class CommentThreadComponent {
  @Input() comment!: Comment;
  @Output() reply = new EventEmitter<{author: string, parentId: string}>();

  /**
   * Handle reply button click
   */
  onReply(): void {
    this.reply.emit({
      author: this.comment.author.node.name,
      parentId: this.comment.id
    });
  }

  /**
   * Handle nested reply events
   */
  onNestedReply(replyData: {author: string, parentId: string}): void {
    this.reply.emit(replyData);
  }

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
   * Get initials from name for avatar placeholder
   */
  getInitials(name: string): string {
    return name
      .split(' ')
      .map(word => word.charAt(0))
      .join('')
      .substring(0, 2)
      .toUpperCase();
  }

  /**
   * Track by function for *ngFor performance
   */
  trackByCommentId(index: number, comment: Comment): string {
    return comment.id;
  }
}
