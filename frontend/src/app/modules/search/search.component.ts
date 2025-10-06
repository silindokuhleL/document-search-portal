import { Component, OnInit } from '@angular/core';
import { FormControl } from '@angular/forms';
import { debounceTime, distinctUntilChanged, switchMap } from 'rxjs/operators';
import { SearchService } from '../../services/search.service';
import { DocumentService } from '../../services/document.service';
import { SearchResult } from '../../models/document.model';
import { of } from 'rxjs';

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
    // Setup search with debounce
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
      return;
    }

    this.isSearching = true;

    this.searchService.search(query, this.sortBy, this.currentPage, this.pageSize)
      .subscribe({
        next: (response) => {
          this.searchResults = response.results;
          this.totalResults = response.total;
          this.totalPages = response.totalPages;
          this.searchTime = response.searchTime;
          this.isSearching = false;
        },
        error: (error) => {
          console.error('Search error:', error);
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
    this.searchControl.setValue(suggestion);
    this.suggestions = [];
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
