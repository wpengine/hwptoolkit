import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { formatDate } from '../../../utils/utils';
import { Comment } from '../../../interfaces/comment.interface';

@Component({
  selector: 'app-comment-thread',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './comment-thread.component.html',
  styleUrls: ['./comment-thread.component.scss'],
})
export class CommentThreadComponent {
  @Input() comment!: Comment;
  @Output() reply = new EventEmitter<{ author: string; parentId: string }>();

  onReply(): void {
    this.reply.emit({
      author: this.comment.author.node.name,
      parentId: this.comment.id,
    });
  }

  onNestedReply(replyData: { author: string; parentId: string }): void {
    this.reply.emit(replyData);
  }

  formatDate(dateString: string): string {
    return formatDate(dateString);
  }

  getInitials(name: string): string {
    return name
      .split(' ')
      .map((word) => word.charAt(0))
      .join('')
      .substring(0, 2)
      .toUpperCase();
  }

  trackByCommentId(index: number, comment: Comment): string {
    return comment.id;
  }
}
