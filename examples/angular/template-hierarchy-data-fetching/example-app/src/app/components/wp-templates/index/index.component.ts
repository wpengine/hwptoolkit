import { Component, OnInit, Input, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { GraphQLService, gql } from '../../../utils/graphql.service';
import { LoadingComponent } from '../../loading/loading.component';
import { NotFoundComponent } from '../../not-found/not-found.component';

// Define interfaces for the index data structure
interface NodeWithTitle {
  title: string;
}

interface NodeWithContentEditor {
  content: string;
}

interface IndexNode extends Partial<NodeWithTitle>, Partial<NodeWithContentEditor> {
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
    NotFoundComponent
  ],
  templateUrl: './index.component.html',
  styleUrl: './index.component.scss'
})
export class IndexComponent implements OnInit {
  @Input() templateData?: any;
  @Input() seedQuery?: any;

  // State signals
  data = signal<IndexResponse | null>(null);
  loading = signal(true);
  error = signal<any>(null);

  // GraphQL Query
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

  // Computed properties using Angular signals
  node = computed(() => {
    return this.data()?.nodeByUri || null;
  });

  hasContent = computed(() => {
    const currentNode = this.node();
    return !!(currentNode?.content?.trim());
  });

  constructor(
    private graphqlService: GraphQLService,
    private router: Router
  ) {}

  ngOnInit(): void {
    console.log('📄 Index component initialized');
    
    // Use seed query if available
    if (this.seedQuery?.nodeByUri) {
      console.log('📋 Using seed query data for index');
      this.data.set({ nodeByUri: this.seedQuery.nodeByUri });
      this.loading.set(false);
    } else {
      this.loadIndex();
    }
  }

  private loadIndex(): void {
    // Get URI from router or template data
    const uri = this.templateData?.uri || this.router.url || '/';
    
    console.log('🔍 Loading index for URI:', uri);
    
    this.loading.set(true);
    this.error.set(null);

    this.graphqlService.query<IndexResponse>(this.INDEX_QUERY, { uri }).subscribe({
      next: (response) => {
        console.log('✅ Index data loaded:', response);
        this.data.set(response);
        this.loading.set(false);
      },
      error: (error) => {
        console.error('❌ Error loading index:', error);
        this.error.set(error);
        this.loading.set(false);
      }
    });
  }

  /**
   * Retry loading the index
   */
  retry(): void {
    this.loadIndex();
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