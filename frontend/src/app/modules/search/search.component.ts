import { of } from 'rxjs';
import { FormControl } from '@angular/forms';
import { Component, OnInit } from '@angular/core';
import { SearchResult } from '../../models/document.model';
import { SearchService } from '../../services/search.service';
import { DocumentService } from '../../services/document.service';
import { debounceTime, distinctUntilChanged, timeout, catchError } from 'rxjs/operators';

@Component({
  selector: 'app-search',
  templateUrl: './search.component.html',
  styleUrls: ['./search.component.scss']
})
export class SearchComponent implements OnInit {
  searchControl = new FormControl('');
  searchResults: SearchResult[] = [];
  suggestions: string[] = [];
  isSearching = false;
  searchTime = 0;
  totalResults = 0;
  
  // Pagination
  currentPage = 1;
  pageSize = 10;
  totalPages = 0;
  
  // Sorting
  sortBy: 'relevance' | 'date' = 'relevance';

  constructor(
    private searchService: SearchService,
    private documentService: DocumentService
  ) {}

  ngOnInit(): void {
    // Setup search with debouncing
    this.searchControl.valueChanges
      .pipe(
        debounceTime(300),
        distinctUntilChanged()
      )
      .subscribe(query => {
        if (query && query.length >= 2) {
          this.currentPage = 1;
          this.performSearch(query);
          this.loadSuggestions(query);
        } else {
          this.searchResults = [];
          this.suggestions = [];
          this.totalResults = 0;
        }
      });
  }

  performSearch(query: string): void {
    if (!query || query.trim().length === 0) {
      this.searchResults = [];
      this.totalResults = 0;
      return;
    }

    this.isSearching = true;

    this.searchService.search(query, this.sortBy, this.currentPage, this.pageSize)
      .pipe(
        timeout(30000), // 30 second timeouts
        catchError(error => {
          console.error('Search error:', error);
          return of({
            results: [],
            total: 0,
            page: this.currentPage,
            limit: this.pageSize,
            totalPages: 0,
            searchTime: 0
          });
        })
      )
      .subscribe({
        next: (response) => {
          this.searchResults = response.results || [];
          this.totalResults = response.total || 0;
          this.totalPages = response.totalPages || 0;
          this.searchTime = response.searchTime || 0;
          this.isSearching = false;
        },
        error: (error) => {
          console.error('Search error:', error);
          this.searchResults = [];
          this.totalResults = 0;
          this.isSearching = false;
        },
        complete: () => {
          // Ensure loading stops even if something goes wrong
          this.isSearching = false;
        }
      });
  }

  loadSuggestions(query: string): void {
    this.searchService.getSuggestions(query, 5).subscribe({
      next: (response) => {
        this.suggestions = response.suggestions;
      },
      error: (error) => {
        console.error('Suggestions error:', error);
      }
    });
  }

  onSortChange(): void {
    const query = this.searchControl.value;
    if (query) {
      this.currentPage = 1;
      this.performSearch(query);
    }
  }

  onPageChange(event: any): void {
    this.currentPage = event.pageIndex + 1;
    this.pageSize = event.pageSize;
    const query = this.searchControl.value;
    if (query) {
      this.performSearch(query);
    }
  }

  selectSuggestion(suggestion: string): void {
    this.suggestions = [];
    this.searchControl.setValue(suggestion);
    this.currentPage = 1;
    this.performSearch(suggestion);
  }

  downloadDocument(result: SearchResult): void {
    this.documentService.downloadDocument(result.id).subscribe({
      next: (blob) => {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = result.original_filename;
        link.click();
        window.URL.revokeObjectURL(url);
      },
      error: (error) => {
        alert('Failed to download document');
      }
    });
  }

  formatFileSize(bytes: number): string {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
  }

  getFileIcon(fileType: string): string {
    if (fileType.includes('pdf')) return 'picture_as_pdf';
    if (fileType.includes('word') || fileType.includes('document')) return 'description';
    if (fileType.includes('text')) return 'text_snippet';
    return 'insert_drive_file';
  }
}
