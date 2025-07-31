import { CommonModule } from '@angular/common';
import {
  Component,
  OnInit,
  OnDestroy,
  signal,
  Type,
  Inject,
  PLATFORM_ID,
} from '@angular/core';
import { RouterModule, Router, NavigationEnd } from '@angular/router';
import { isPlatformBrowser } from '@angular/common';
import { Subscription, Subject } from 'rxjs';
import { filter, takeUntil } from 'rxjs/operators';
import {
  TemplateHierarchyService,
  TemplateData,
} from '../../utils/templateHierarchy';
import { NotFoundComponent } from '../not-found/not-found.component';
import { LoadingComponent } from '../loading/loading.component';
@Component({
  selector: 'app-dynamic-content',
  standalone: true,
  imports: [CommonModule, RouterModule, LoadingComponent, NotFoundComponent],

  templateUrl: './dynamic-content.component.html',
})
export class DynamicContentComponent implements OnInit, OnDestroy {
  // Use signals for reactive state
  isLoading = signal(true);
  hasError = signal(false);
  componentToRender = signal<Type<any> | null>(null);
  templateData = signal<TemplateData | null>(null);

  private routerEventsSubscription: Subscription | undefined;
  private destroy$ = new Subject<void>(); // Add destruction subject
  private currentRequest: Promise<any> | null = null; // Track current request

  // Dynamic import map for template components
  private templateComponentMap: { [key: string]: () => Promise<any> } = {
    'front-page': () =>
      import('../wp-templates/front-page/front-page.component').then(
        (m) => m.FrontPageComponent
      ),
    home: () =>
      import('../wp-templates/home/home.component').then(
        (m) => m.HomeComponent
      ),
    page: () =>
      import('../wp-templates/page/page.component').then(
        (m) => m.PageComponent
      ),
    singular: () =>
      import('../wp-templates/singular/singular.component').then(
        (m) => m.SingularComponent
      ),
    archive: () =>
      import('../wp-templates/archive/archive.component').then(
        (m) => m.ArchiveComponent
      ),
  };

  constructor(
    private router: Router,
    private templateHierarchyService: TemplateHierarchyService,
    @Inject(PLATFORM_ID) private platformId: Object
  ) {}

  ngOnInit(): void {
    // Subscribe to router events with proper cleanup
    this.routerEventsSubscription = this.router.events
      .pipe(
        filter((event) => event instanceof NavigationEnd),
        takeUntil(this.destroy$) // Automatically unsubscribe on destroy
      )
      .subscribe((event: NavigationEnd) => {
        //console.log('🔄 Router navigation detected:', event.url);
        this.loadContent();
      });

    // Initial load with proper route resolution
    this.loadContent();
  }

  ngOnDestroy(): void {
    //console.log('🧹 DynamicContentComponent destroyed');

    // Cancel template hierarchy service requests
    this.templateHierarchyService.cancelAllRequests();

    // Complete the destroy subject to cancel all subscriptions
    this.destroy$.next();
    this.destroy$.complete();

    // Clean up router subscription
    if (this.routerEventsSubscription) {
      this.routerEventsSubscription.unsubscribe();
    }

    // Cancel any pending requests
    this.currentRequest = null;
  }

  private getCurrentUri(): string {
    const routerUrl = this.router.url;

    // Only access window in browser environment
    if (isPlatformBrowser(this.platformId)) {
      const actualPath = window.location.pathname;

      if (routerUrl === '/' && actualPath !== '/') return actualPath;
    }

    return routerUrl;
  }

  private async loadContent(): Promise<void> {
    const uri = this.getCurrentUri();

    // Cancel any existing request
    if (this.currentRequest) {
      this.currentRequest = null;
    }

    try {
      //console.group('🎯 Loading Dynamic Content');

      // Clear everything immediately to prevent stacking
      this.componentToRender.set(null);
      this.templateData.set(null);
      this.hasError.set(false);
      this.isLoading.set(true);

      // Check if component is still alive
      if (this.destroy$.closed) {
        return;
      }

      // Create and track the current request with cancellation token
      this.currentRequest = this.templateHierarchyService.uriToTemplate({
        uri,
        cancelToken: this.destroy$, // ✅ Pass the destroy signal as cancel token
      });

      // Resolve template using your service
      const resolvedTemplateData = await this.currentRequest;

      // Check if this request is still current and component is alive
      if (this.destroy$.closed || !this.currentRequest) {
        return;
      }

      //console.log('✅ Template data resolved:', resolvedTemplateData);

      if (resolvedTemplateData?.template) {
        const templateId = resolvedTemplateData.template.id;
        //console.log('🎨 Template ID:', templateId);

        // Get the dynamic import function
        const importFunction = this.templateComponentMap[templateId];

        if (importFunction) {
          //console.log('📦 Loading component for template:', templateId);

          // Dynamically import the component
          const componentClass = await importFunction();

          // Final check before setting state
          if (this.destroy$.closed) {
            return;
          }
          // Check if we're still on the same route
          const currentUri = this.getCurrentUri();
          if (
            currentUri === uri &&
            !this.destroy$.closed &&
            resolvedTemplateData
          ) {
            this.componentToRender.set(componentClass);
            this.templateData.set(resolvedTemplateData);
          }
        } else {
          //console.log(
          //   'Available templates:',
          //   Object.keys(this.templateComponentMap)
          // );

          // Fallback to 404
          const currentUri = this.getCurrentUri();
          if (currentUri === uri && !this.destroy$.closed) {
            this.componentToRender.set(NotFoundComponent);
            this.hasError.set(true);
          }
        }
      } else {
        const currentUri = this.getCurrentUri();
        if (currentUri === uri && !this.destroy$.closed) {
          this.componentToRender.set(NotFoundComponent);
          this.hasError.set(true);
        }
      }
    } catch (error) {
      // Check if component is still alive before setting error state
      if (!this.destroy$.closed) {
        const currentUri = this.getCurrentUri();
        if (currentUri === uri) {
          this.componentToRender.set(NotFoundComponent);
          this.hasError.set(true);
        }
      }
    } finally {
      // Only update loading state if component is still alive
      if (!this.destroy$.closed) {
        this.isLoading.set(false);
        //console.groupEnd();
      }
      this.currentRequest = null;
    }
  }
}
