import { Component, OnInit, Input, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { fetchGraphQL, gql } from '../../../utils/graphql.service';
import { LoadingComponent } from '../../loading/loading.component';
import { NotFoundComponent } from '../../not-found/not-found.component';
import { EmptyStateComponent } from '../../empty-state/empty-state.component';

interface NodeWithTitle {
  title: string;
}

interface NodeWithContentEditor {
  content: string;
}

interface IndexNode
  extends Partial<NodeWithTitle>,
    Partial<NodeWithContentEditor> {
  __typename: string;
  uri: string;
  id: string;
}

interface IndexResponse {
  nodeByUri: IndexNode | null;
}

@Component({
  selector: 'app-index',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    LoadingComponent,
    NotFoundComponent,
    EmptyStateComponent,
  ],
  templateUrl: './index.component.html',
  styleUrl: './index.component.scss',
})
export class IndexComponent implements OnInit {
  data = signal<IndexResponse | null>(null);
  loading = signal(true);
  error = signal<any>(null);

  private INDEX_QUERY = gql`
    query indexTemplateNodeQuery($uri: String!) {
      nodeByUri(uri: $uri) {
        __typename
        uri
        id
        ... on NodeWithTitle {
          title
        }
        ... on NodeWithContentEditor {
          content
        }
      }
    }
  `;

  node = computed(() => {
    return this.data()?.nodeByUri || null;
  });

  hasContent = computed(() => {
    const currentNode = this.node();
    return !!currentNode?.content?.trim();
  });

  constructor(private router: Router) {}

  ngOnInit(): void {
    this.loadIndex();
  }

  private loadIndex(): void {
    const uri = this.router.url;

    this.loading.set(true);
    this.error.set(null);

    fetchGraphQL<IndexResponse>(this.INDEX_QUERY, { uri })
      .then((response) => {
        this.data.set(response);
      })
      .catch((error) => {
        this.error.set(error);
      })
      .finally(() => {
        this.loading.set(false);
      });
  }

  /**
   * Get the node title with fallback
   */
  getNodeTitle(): string {
    const currentNode = this.node();
    return currentNode?.title || 'Untitled';
  }

  /**
   * Check if node exists
   */
  hasNode(): boolean {
    return !!this.node();
  }
}
