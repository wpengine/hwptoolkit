import {
  Component,
  Input,
  Output,
  EventEmitter,
  computed,
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { Comment } from '../../../interfaces/comment.interface';
import { formatDate } from '../../../utils/utils';

@Component({
  selector: 'app-comment-item',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './comment-item.component.html',
  styleUrl: './comment-item.component.scss',
})
export class CommentItemComponent {
  @Input() comment!: Comment;
  @Output() reply = new EventEmitter<Comment>();

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

  formatDate(dateString: string): string {
    return formatDate(dateString);
  }

  onReply(): void {
    this.reply.emit(this.comment);
  }
}
