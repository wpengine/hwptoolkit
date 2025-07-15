// src/app/services/template-hierarchy.service.ts

import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import {
  getTemplate,
  getPossibleTemplates,
  type WordPressTemplate,
} from "./templates";
import { SEED_QUERY } from "./seedQuery";
import { TemplateDiscoveryService } from '../../services/template-discovery.service'; // Import the new service

export type TemplateData = {
  uri: string;
  seedQuery: any;
  availableTemplates: Array<{ id: string; path: string }>; // Stronger typing for availableTemplates
  possibleTemplates: string[];
  template: WordPressTemplate;
};

export interface GraphQLResponse<T = any> {
  data: T;
  errors?: Array<{
    message: string;
    locations?: Array<{ line: number; column: number }>;
    path?: string[];
  }>;
}

/**
 * Angular service for resolving WordPress template hierarchy
 * Converts URIs to appropriate WordPress templates using GraphQL and template matching
 */
@Injectable({
  providedIn: 'root'
})
export class TemplateHierarchyService {
  private wpUrl = environment.wordpressUrl || 'http://localhost:8892';
  private graphqlEndpoint = `${this.wpUrl}/graphql`;

  constructor(
    private http: HttpClient,
    private templateDiscoveryService: TemplateDiscoveryService // Inject TemplateDiscoveryService
  ) {}

  /**
   * Resolves a URI to its corresponding WordPress template data by querying the GraphQL endpoint
   * and determining the appropriate template based on the content type and available templates.
   *
   * @param params - The function parameters
   * @param params.uri - The URI path to resolve (e.g., "/about", "/blog/post-slug")
   *
   * @returns A promise that resolves to template data containing:
   * - uri: The original URI that was resolved
   * - seedQuery: Raw data from the WordPress GraphQL query
   * - availableTemplates: List of template files available in the system
   * - possibleTemplates: Templates that could be used for this content type
   * - template: The final selected template to render this URI
   *
   * @throws {Error} With status 404 if the URI is not found in WordPress
   * @throws {Error} With status 500 if:
   * - GraphQL query fails
   * - No templates are available
   * - No possible templates match the content type
   * - No final template can be determined
   *
   * @example
   * const templateData = await this.templateHierarchy.uriToTemplate({ uri: "/about" });
   */
  async uriToTemplate({ uri }: { uri: string }): Promise<TemplateData> {
    try {
      // Debug logging
      console.group('🎯 Template Hierarchy Resolution');
      console.log('📍 URI:', uri);
      console.log('🌐 GraphQL Endpoint:', this.graphqlEndpoint);

      // Fetch seed query data from WordPress GraphQL
      const seedQueryResponse = await this.fetchSeedQuery(uri);

      if (!seedQueryResponse.data?.nodeByUri) {
        console.error('❌ HTTP/404 - Not Found in WordPress:', uri);
        throw new Error(`URI not found in WordPress: ${uri}`);
      }

      // Fetch available templates using the injected service
      const availableTemplates = await this.fetchAvailableTemplates(); // No URI needed here for templates API

      if (!availableTemplates || availableTemplates.length === 0) {
        console.error('❌ No templates found');
        throw new Error('No available templates');
      }

      // Get possible templates for this content type
      const possibleTemplates = getPossibleTemplates(seedQueryResponse.data.nodeByUri);

      if (!possibleTemplates || possibleTemplates.length === 0) {
        console.error('❌ No possible templates found for content type');
        throw new Error('No possible templates for this URI');
      }

      // Select the best template
      const template = getTemplate(availableTemplates, possibleTemplates);

      if (!template) {
        console.error('❌ No template found for route');
        throw new Error('No template found for this URI');
      }

      console.log('✅ Template resolved:', template);
      console.log('📋 Possible templates:', possibleTemplates);
      console.groupEnd();

      return {
        uri,
        seedQuery: seedQueryResponse.data,
        availableTemplates,
        possibleTemplates,
        template,
      };

    } catch (error) {
      console.groupEnd();
      console.error('❌ Template hierarchy resolution failed:', error);
      throw error;
    }
  }

  /**
   * Fetch seed query data from WordPress GraphQL endpoint
   */
  private async fetchSeedQuery(uri: string): Promise<GraphQLResponse> {
    try {
      const body = {
        query: SEED_QUERY,
        variables: { uri },
      };

      console.log('📤 Fetching seed query for URI:', uri);

      const response = await firstValueFrom(
        this.http.post<GraphQLResponse>(this.graphqlEndpoint, body, {
          headers: {
            'Content-Type': 'application/json',
          },
        })
      );

      if (response.errors && response.errors.length > 0) {
        console.error('GraphQL errors:', response.errors);
        throw new Error(`GraphQL error: ${response.errors[0].message}`);
      }

      return response;

    } catch (error) {
      console.error('Error fetching seed query:', error);
      throw new Error(`Failed to fetch seed query: ${error}`);
    }
  }

  /**
   * Fetch available templates from the backend API using TemplateDiscoveryService.
   */
  private async fetchAvailableTemplates(): Promise<Array<{ id: string; path: string }>> {
    try {
      console.log('📤 Fetching available templates from TemplateDiscoveryService');

      // Use the injected TemplateDiscoveryService to get the templates
      const templates = await firstValueFrom(
        this.templateDiscoveryService.getAvailableTemplates()
      );

      console.log('📥 Available templates:', templates);
      return templates;

    } catch (error) {
      console.error('Error fetching available templates from backend:', error);

      // Fallback: return default templates if API fails
      console.log('🔄 Using fallback templates');
      return this.getDefaultTemplates();
    }
  }

  /**
   * Fallback method to provide default templates when API is unavailable
   * Ensure these match the expected structure: { id: string, path: string }
   */
  private getDefaultTemplates(): Array<{ id: string; path: string }> {
    // These should ideally match the 'id' (folder name) that your Angular components expect
    // and a conceptual 'path' that helps identify them.
    return [
      { id: 'index', path: '/src/app/components/wp-templates/index' },
      { id: 'single', path: '/src/app/components/wp-templates/single' },
      { id: 'page', path: '/src/app/components/wp-templates/page' },
      { id: 'archive', path: '/src/app/components/wp-templates/archive' },
      { id: 'front-page', path: '/src/app/components/wp-templates/front-page' },
    ];
  }

  /**
   * Utility method to check if a URI exists in WordPress
   */
  async checkUriExists(uri: string): Promise<boolean> {
    try {
      const response = await this.fetchSeedQuery(uri);
      return !!response.data?.nodeByUri;
    } catch {
      return false;
    }
  }

  /**
   * Get template suggestions for a given URI without full resolution
   */
  async getTemplateSuggestions(uri: string): Promise<string[]> {
    try {
      const response = await this.fetchSeedQuery(uri);
      if (response.data?.nodeByUri) {
        return getPossibleTemplates(response.data.nodeByUri);
      }
      return [];
    } catch {
      return [];
    }
  }
}

/**
 * Standalone function for backward compatibility
 * @deprecated Use TemplateHierarchyService instead
 */
export async function uriToTemplate({ uri }: { uri: string }): Promise<TemplateData> {
  console.warn('⚠️ uriToTemplate function is deprecated. Use TemplateHierarchyService instead.');

  // This is a simplified version for backward compatibility
  // In a real Angular app, you should inject the service
  throw new Error('This function requires dependency injection. Use TemplateHierarchyService instead.');
}