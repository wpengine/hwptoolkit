import {
  Component,
  Input,
  Output,
  EventEmitter,
  signal,
  effect,
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { gql, executeMutation } from '../../../utils/graphql.service';
import {
  ReplyData,
  CommentFormData,
} from '../../../interfaces/comment.interface';

@Component({
  selector: 'app-comment-form',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './comment-form.component.html',
  styleUrls: ['./comment-form.component.scss'],
})
export class CommentFormComponent {
  @Input() postId!: number;
  @Input() replyData: ReplyData | null = null;

  @Output() submit = new EventEmitter<CommentFormData>();
  @Output() error = new EventEmitter<Error>();
  @Output() cancel = new EventEmitter<void>();

  isSubmitting = signal<boolean>(false);
  formError = signal<Error | null>(null);
  showSuccess = signal<boolean>(false);

  formData: CommentFormData = {
    author: '',
    email: '',
    url: '',
    content: '',
  };

  private CREATE_COMMENT = gql`
    mutation CreateComment(
      $commentOn: Int!
      $content: String!
      $author: String!
      $authorEmail: String!
      $parent: ID
    ) {
      createComment(
        input: {
          commentOn: $commentOn
          content: $content
          author: $author
          authorEmail: $authorEmail
          parent: $parent
        }
      ) {
        success
        comment {
          id
          content
          date
          parentId
          author {
            node {
              name
            }
          }
        }
      }
    }
  `;

  constructor() {
    effect(() => {
      if (this.showSuccess()) {
        setTimeout(() => {
          this.showSuccess.set(false);
        }, 5000);
      }
    });
  }

  async onSubmit(event: Event): Promise<void> {
    event.preventDefault();

    if (this.isSubmitting()) {
      return;
    }

    if (
      !this.formData.author ||
      !this.formData.email ||
      !this.formData.content
    ) {
      this.formError.set(new Error('Please fill in all required fields.'));
      return;
    }

    try {
      this.isSubmitting.set(true);
      this.formError.set(null);
      this.showSuccess.set(false);

      this.submit.emit({ ...this.formData });

      let variables: any = {
        commentOn: this.postId,
        content: this.formData.content,
        author: this.formData.author,
        authorEmail: this.formData.email,
        parent: null,
      };

      if (this.replyData?.parentId) {
        try {
          const base64ParentId = atob(this.replyData.parentId);
          const parentNumber = parseInt(base64ParentId.split(':')[1], 10);
          variables.parent = parentNumber;
        } catch {
          variables.parent = this.replyData.parentId;
        }
      }

      const result = await executeMutation(this.CREATE_COMMENT, variables);

      if (result.errors && result.errors.length > 0) {
        throw new Error(
          result.errors[0]?.message || 'Failed to submit comment'
        );
      }

      if (!result.data?.createComment?.success) {
        throw new Error('Comment submission failed');
      }

      this.showSuccess.set(true);

      this.resetForm();
    } catch (error: any) {
      const errorMessage = error.message || 'Failed to submit comment';
      const commentError = new Error(errorMessage);

      this.formError.set(commentError);
    } finally {
      this.isSubmitting.set(false);
    }
  }

  onCancel(): void {
    this.cancel.emit();
    this.resetForm();
  }

  clearError(): void {
    this.formError.set(null);
  }

  private resetForm(): void {
    this.formData = {
      author: '',
      email: '',
      url: '',
      content: '',
    };
  }

  get isFormValid(): boolean {
    return !!(
      this.formData.author.trim() &&
      this.formData.email.trim() &&
      this.formData.content.trim() &&
      this.isValidEmail(this.formData.email)
    );
  }

  private isValidEmail(email: string): boolean {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }
}
