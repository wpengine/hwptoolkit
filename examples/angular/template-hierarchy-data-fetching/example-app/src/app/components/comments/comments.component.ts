import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-comments',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="comments-container">
      <h3>Comments</h3>
      <p>Comments for {{ contentType }} ID: {{ postId }}</p>
      <!-- Add your comment implementation here -->
    </div>
  `,
  styles: [`
    .comments-container {
      margin-top: 2rem;
      padding: 1.5rem;
      background-color: #f8f9fa;
      border-radius: 8px;
    }
    h3 {
      margin-bottom: 1rem;
    }
  `]
})
export class CommentsComponent {
  @Input() postId!: number;
  @Input() contentType: string = 'post';
}