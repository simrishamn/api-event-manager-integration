import { Button } from 'hbg-react';
import PropTypes from 'prop-types';
import AgeFilter from './AgeFilter';
import CategoriesFilter from './CategoriesFilter';
import DateFilter from './DateFilter';
import SearchBar from './SearchBar';

const FilterContainer = ({
  ageRange,
  categories,
  endDate,
  formatDate,
  fromDateChange,
  onAgeChange,
  onCategoryChange,
  onSubmit,
  onTagChange,
  onFilterReset,
  searchString,
  settings,
  startDate,
  tags,
  toDateChange,
  translation,
  updateSearchString,
}) => (
  <form onSubmit={onSubmit}>
    <div className="grid">
      {settings.mod_event_filter_search && (
        <div className="grid-md-12 grid-lg-auto u-mb-2 u-mb-2@md u-mb-0@lg u-mb-0@xl">
          <SearchBar
            translation={translation}
            searchString={searchString}
            updateSearchString={updateSearchString}
          />
        </div>
      )}

      {settings.mod_event_filter_dates && (
        <div className="grid-sm-12 grid-md-6 grid-lg-auto u-mb-2 u-mb-2@md u-mb-0@lg u-mb-0@xl">
          <DateFilter
            id="filter-date-from"
            label={`${translation.from} ${translation.date}`}
            onDayChange={fromDateChange}
            formatDate={formatDate}
            value={startDate}
          />
        </div>
      )}

      {settings.mod_event_filter_dates && (
        <div className="grid-sm-12 grid-md-6 grid-lg-auto u-mb-2 u-mb-2@md u-mb-0@lg u-mb-0@xl">
          <DateFilter
            id="filter-date-to"
            label={`${translation.to} ${translation.date}`}
            onDayChange={toDateChange}
            formatDate={formatDate}
            value={endDate}
          />
        </div>
      )}

      {settings.mod_event_filter_age_group && ageRange.length > 0 && (
        <div className="grid-fit-content u-mb-2 u-mb-2@md u-mb-0@lg u-mb-0@xl">
          <AgeFilter translation={translation} ageRange={ageRange} onAgeChange={onAgeChange} />
        </div>
      )}

      {settings.mod_event_filter_categories && categories.length > 0 && (
        <div className="grid-fit-content u-mr-auto u-mb-2 u-mb-2@md u-mb-0@lg u-mb-0@xl">
          <CategoriesFilter
            title={translation.categories}
            categories={categories}
            onCategoryChange={onCategoryChange}
          />
        </div>
      )}

      {settings.mod_event_filter_tags && tags.length > 0 && (
        <div className="grid-fit-content u-mr-auto u-mb-2 u-mb-2@md u-mb-0@lg u-mb-0@xl">
          <CategoriesFilter
            title={translation.tags}
            categories={tags}
            onCategoryChange={onTagChange}
          />
        </div>
      )}

      <div className="grid-fit-content">
        <button className="btn btn-theme-first" type="button" onClick={onFilterReset}>
          {translation.resetSearch}
        </button>
      </div>

      <div className="grid-fit-content">
        <Button title={translation.search} color="primary" submit />
      </div>
    </div>
  </form>
);

FilterContainer.propTypes = {
  ageRange: PropTypes.array,
  categories: PropTypes.array,
  endDate: PropTypes.string,
  formatDate: PropTypes.func,
  fromDateChange: PropTypes.func,
  onAgeChange: PropTypes.func,
  onCategoryChange: PropTypes.func,
  onSubmit: PropTypes.func.isRequired,
  onTagChange: PropTypes.func,
  searchString: PropTypes.string,
  settings: PropTypes.object.isRequired,
  startDate: PropTypes.string,
  tags: PropTypes.array,
  toDateChange: PropTypes.func,
  translation: PropTypes.object.isRequired,
  updateSearchString: PropTypes.func,
};

FilterContainer.defaultProps = {
  ageRange: [],
  categories: [],
  tags: [],
};

export default FilterContainer;
