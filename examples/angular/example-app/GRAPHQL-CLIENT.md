# Angular GraphQL Client

This is a comprehensive GraphQL client implementation for Angular, converted from Vue/Nuxt composables to Angular services with reactive state management using Angular Signals.

## Features

- ✅ **Angular Services** - Injectable services for dependency injection
- ✅ **Reactive State Management** - Using Angular Signals for reactive updates
- ✅ **RxJS Integration** - Observable-based API with proper error handling
- ✅ **TypeScript Support** - Full type safety with interfaces and generics
- ✅ **Caching Support** - Built-in query caching with shareReplay
- ✅ **Loading States** - Automatic loading state management
- ✅ **Error Handling** - Comprehensive error handling and logging
- ✅ **Standalone Functions** - Utility functions for simple operations

## Services

### 1. GraphQLService

Core service for executing GraphQL queries and mutations.

```typescript
import { GraphQLService } from './shared/utils/graphql.service';

constructor(private graphqlService: GraphQLService) {}

// Execute a query
this.graphqlService.query(query, variables).subscribe({
  next: (data) => console.log(data),
  error: (error) => console.error(error)
});

// Execute a mutation
this.graphqlService.mutate(mutation, variables).subscribe({
  next: (result) => console.log(result.data),
  error: (error) => console.error(error)
});
```

### 2. GraphQLStateService

Advanced service with reactive state management using Angular Signals.

```typescript
import { GraphQLStateService } from './shared/utils/graphql.service';

constructor(private graphqlState: GraphQLStateService) {}

ngOnInit() {
  // Create reactive query
  const postsQuery = this.graphqlState.createQuery(query, variables);
  
  // Access reactive signals
  this.data = postsQuery.data;
  this.loading = postsQuery.loading;
  this.error = postsQuery.error;
  
  // Refetch data
  postsQuery.refetch().subscribe();
  
  // Update variables
  postsQuery.setVariables({ first: 20 }).subscribe();
}
```

## Usage Examples

### Basic Query Component

```typescript
import { Component, OnInit, signal } from '@angular/core';
import { GraphQLStateService, gql } from '../../shared/utils/graphql.service';

@Component({
  selector: 'app-posts',
  template: `
    <div *ngIf="loading()">Loading...</div>
    <div *ngIf="error()">Error: {{ error() }}</div>
    <div *ngFor="let post of posts()?.posts?.nodes">
      <h3>{{ post.title }}</h3>
      <p>{{ post.excerpt }}</p>
    </div>
  `
})
export class PostsComponent implements OnInit {
  posts = signal(null);
  loading = signal(false);
  error = signal(null);

  constructor(private graphqlState: GraphQLStateService) {}

  ngOnInit() {
    const query = this.graphqlState.createQuery(
      gql`
        query GetPosts {
          posts {
            nodes {
              id
              title
              excerpt
            }
          }
        }
      `
    );

    this.posts = query.data;
    this.loading = query.loading;
    this.error = query.error;
  }
}
```

### Mutation Example

```typescript
import { Component } from '@angular/core';
import { GraphQLStateService, gql } from '../../shared/utils/graphql.service';

@Component({
  selector: 'app-comment-form',
  template: `
    <form (ngSubmit)="submitComment()">
      <textarea [(ngModel)]="commentText"></textarea>
      <button type="submit" [disabled]="loading()">
        {{ loading() ? 'Submitting...' : 'Submit Comment' }}
      </button>
    </form>
    <div *ngIf="error()">Error: {{ error() }}</div>
  `
})
export class CommentFormComponent {
  commentText = '';
  
  private commentMutation = this.graphqlState.createMutation(
    gql`
      mutation CreateComment($input: CreateCommentInput!) {
        createComment(input: $input) {
          comment {
            id
            content
          }
        }
      }
    `
  );

  loading = this.commentMutation.loading;
  error = this.commentMutation.error;

  constructor(private graphqlState: GraphQLStateService) {}

  submitComment() {
    this.commentMutation.execute({
      input: {
        content: this.commentText,
        postId: 'post-id'
      }
    }).subscribe({
      next: (result) => {
        console.log('Comment created:', result.data);
        this.commentText = ''; // Reset form
      }
    });
  }
}
```

### Standalone Function Usage

```typescript
import { fetchGraphQL, executeMutation, gql } from './shared/utils/graphql.service';

// Simple query
async function getPosts() {
  try {
    const data = await fetchGraphQL(
      gql`
        query GetPosts {
          posts {
            nodes {
              id
              title
            }
          }
        }
      `
    );
    return data.posts.nodes;
  } catch (error) {
    console.error('Error fetching posts:', error);
    throw error;
  }
}

// Simple mutation
async function createPost(title: string, content: string) {
  try {
    const result = await executeMutation(
      gql`
        mutation CreatePost($input: CreatePostInput!) {
          createPost(input: $input) {
            post {
              id
              title
            }
          }
        }
      `,
      {
        input: { title, content }
      }
    );
    
    if (result.errors) {
      throw new Error(result.errors[0].message);
    }
    
    return result.data;
  } catch (error) {
    console.error('Error creating post:', error);
    throw error;
  }
}
```

## Configuration

### Environment Setup

Update your environment files with your WordPress GraphQL endpoint:

```typescript
// src/environments/environment.ts
export const environment = {
  production: false,
  wordpressUrl: 'http://localhost:8080'
};

// src/environments/environment.prod.ts
export const environment = {
  production: true,
  wordpressUrl: 'https://your-wordpress-site.com'
};
```

### App Module Setup

Make sure to import HttpClientModule in your app configuration:

```typescript
// app.config.ts
import { ApplicationConfig } from '@angular/core';
import { provideHttpClient } from '@angular/common/http';

export const appConfig: ApplicationConfig = {
  providers: [
    provideHttpClient(),
    // other providers
  ]
};
```

## Key Differences from Vue/Nuxt Version

| Vue/Nuxt Feature | Angular Equivalent | Notes |
|------------------|-------------------|-------|
| `ref()`, `computed()` | `signal()` | Angular Signals for reactivity |
| `useRuntimeConfig()` | `environment` | Environment configuration |
| `useState()` | `signal()` | Local component state |
| `onMounted()` | `ngOnInit()` | Lifecycle hooks |
| `onServerPrefetch()` | Angular Universal SSR | Server-side rendering |
| Composables | Injectable Services | Dependency injection pattern |
| `readonly()` | `asReadonly()` | Read-only signals |

## Benefits of Angular Version

1. **Type Safety** - Full TypeScript support with interfaces and generics
2. **Dependency Injection** - Angular's powerful DI system
3. **RxJS Integration** - Reactive programming with Observables
4. **Signal-based Reactivity** - Modern reactive state management
5. **Service Architecture** - Separation of concerns with services
6. **Error Handling** - Comprehensive error handling and logging
7. **Caching** - Built-in query result caching
8. **Testability** - Easy to unit test with Angular testing utilities

## Testing

The services can be easily tested using Angular's testing utilities:

```typescript
import { TestBed } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { GraphQLService } from './graphql.service';

describe('GraphQLService', () => {
  let service: GraphQLService;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [GraphQLService]
    });
    service = TestBed.inject(GraphQLService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
```
