import { CommonModule } from '@angular/common';
import { Component, OnInit, OnDestroy, signal, Type } from '@angular/core';
import { RouterModule, Router, NavigationEnd } from '@angular/router';
import { Subscription } from 'rxjs';
import { filter } from 'rxjs/operators';
import { TemplateHierarchyService, TemplateData } from '../../shared/utils/templateHierarchy';
import { NotFoundComponent } from '../not-found/not-found.component';

@Component({
  selector: 'app-dynamic-content',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    NotFoundComponent
  ],
  templateUrl: './dynamic-content.component.html'
})
export class DynamicContentComponent implements OnInit, OnDestroy {
  // Use signals for reactive state
  isLoading = signal(true);
  hasError = signal(false);
  componentToRender = signal<Type<any> | null>(null);
  templateData = signal<TemplateData | null>(null);

  private routerEventsSubscription: Subscription | undefined;

  // Dynamic import map for template components
  private templateComponentMap: { [key: string]: () => Promise<any> } = {
    'front-page': () => import('../wp-templates/front-page/front-page.component').then(m => m.FrontPageComponent),
    'home': () => import('../wp-templates/home/home.component').then(m => m.HomeComponent),
    'page': () => import('../wp-templates/page/page.component').then(m => m.PageComponent),
    // 'single': () => import('../wp-templates/single/single.component').then(m => m.SingleComponent),
    // 'archive': () => import('../wp-templates/archive/archive.component').then(m => m.ArchiveComponent),
    // 'category': () => import('../wp-templates/archive/archive.component').then(m => m.ArchiveComponent),
    // 'tag': () => import('../wp-templates/archive/archive.component').then(m => m.ArchiveComponent),
    'index': () => import('../wp-templates/home/home.component').then(m => m.HomeComponent), // fallback
    '404': () => Promise.resolve(NotFoundComponent),
  };

  constructor(
    private router: Router,
    private templateHierarchyService: TemplateHierarchyService
  ) {}

  ngOnInit(): void {
    console.log('🚀 DynamicContentComponent initialized');
    
    // Subscribe to router events
    this.routerEventsSubscription = this.router.events.pipe(
      filter(event => event instanceof NavigationEnd)
    ).subscribe((event: NavigationEnd) => {
      console.log('🔄 Router navigation detected:', event.url);
      this.loadContent();
    });

    // Initial load
    this.loadContent();
  }

  ngOnDestroy(): void {
    if (this.routerEventsSubscription) {
      this.routerEventsSubscription.unsubscribe();
    }
  }

  private async loadContent(): Promise<void> {
    try {
      console.group('🎯 Loading Dynamic Content');
      
      this.isLoading.set(true);
      this.hasError.set(false);
      this.componentToRender.set(null);
      this.templateData.set(null);

      const uri = this.router.url;
      console.log('📍 Current URI:', uri);

      // Resolve template using your service
      const resolvedTemplateData = await this.templateHierarchyService.uriToTemplate({ uri });
      console.log('✅ Template data resolved:', resolvedTemplateData);

      if (resolvedTemplateData?.template) {
        const templateId = resolvedTemplateData.template.id;
        console.log('🎨 Template ID:', templateId);

        // Get the dynamic import function
        const importFunction = this.templateComponentMap[templateId];

        if (importFunction) {
          console.log('📦 Loading component for template:', templateId);
          
          // Dynamically import the component
          const componentClass = await importFunction();
          console.log('✅ Component loaded:', componentClass.constructor?.name || componentClass.name);

          // Set the component and data
          this.componentToRender.set(componentClass);
          this.templateData.set(resolvedTemplateData);
        } else {
          console.warn('⚠️ No component mapped for template:', templateId);
          console.log('Available templates:', Object.keys(this.templateComponentMap));
          
          // Fallback to 404
          this.componentToRender.set(NotFoundComponent);
          this.hasError.set(true);
        }
      } else {
        console.error('❌ No template data resolved');
        this.componentToRender.set(NotFoundComponent);
        this.hasError.set(true);
      }

    } catch (error) {
      console.error('❌ Error loading content:', error);
      this.componentToRender.set(NotFoundComponent);
      this.hasError.set(true);
    } finally {
      this.isLoading.set(false);
      console.groupEnd();
    }
  }

  // Helper method for debugging
  getDebugInfo() {
    return {
      isLoading: this.isLoading(),
      hasError: this.hasError(),
      componentToRender: this.componentToRender()?.name || 'None',
      templateData: this.templateData(),
      currentUrl: this.router.url
    };
  }
}