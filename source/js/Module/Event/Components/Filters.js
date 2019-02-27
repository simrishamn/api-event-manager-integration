import DayPickerInput from 'react-day-picker/DayPickerInput';
import { Button, Dropdown } from 'hbg-react';
import { months, weekdaysLong, weekdaysShort } from '../../../Config/dateLocalization.js';

const Filters = ({
    settings,
    translation,
    updateSearchString,
    updateAge,
    onSubmit,
    fromDateChange,
    toDateChange,
    formatDate,
    categories,
    onCategoryChange,
}) => (
    <form onSubmit={onSubmit}>
        <div className="grid">
            {settings.mod_event_filter_search && (
                <div className="grid-md-12 grid-lg-auto u-mb-2 u-mb-2@md u-mb-0@lg u-mb-0@xl">
                    <label htmlFor="filter-keyword" className="text-sm sr-only">
                        <strong>{translation.search}</strong>
                    </label>

                    <div className="input-group">
                        <span className="input-group-addon">
                            <i className="fa fa-search" />
                        </span>
                        <input
                            type="text"
                            id="filter-keyword"
                            className="form-control"
                            onChange={updateSearchString}
                            placeholder={translation.search}
                        />
                    </div>
                </div>
            )}

            {settings.mod_event_filter_dates && (
                <div className="grid-sm-12 grid-md-6 grid-lg-auto u-mb-2 u-mb-2@md u-mb-0@lg u-mb-0@xl">
                    <label htmlFor="filter-date-from" className="text-sm sr-only">
                        <strong>{translation.fromDate}</strong>
                    </label>
                    <div className="input-group">
                        <span className="input-group-addon">{translation.from}:</span>
                        <DayPickerInput
                            id="filter-date-from"
                            onDayChange={day => fromDateChange(day)}
                            placeholder={translation.fromDate}
                            formatDate={formatDate}
                            inputProps={{
                                readOnly: true,
                                type: 'text',
                            }}
                            dayPickerProps={{
                                fromMonth: new Date(),
                                months: months.se,
                                weekdaysLong: weekdaysLong.se,
                                weekdaysShort: weekdaysShort.se,
                                firstDayOfWeek: 1,
                            }}
                        />
                    </div>
                </div>
            )}

            {settings.mod_event_filter_dates && (
                <div className="grid-sm-12 grid-md-6 grid-lg-auto u-mb-2 u-mb-2@md u-mb-0@lg u-mb-0@xl">
                    <label htmlFor="filter-date-to" className="text-sm sr-only">
                        <strong>{translation.toDate}</strong>
                    </label>

                    <div className="input-group">
                        <span className="input-group-addon">{translation.to}:</span>
                        <DayPickerInput
                            id="filter-date-to"
                            onDayChange={day => toDateChange(day)}
                            placeholder={translation.toDate}
                            formatDate={formatDate}
                            inputProps={{
                                readOnly: true,
                                type: 'text',
                            }}
                            dayPickerProps={{
                                fromMonth: new Date(),
                                months: months.se,
                                weekdaysLong: weekdaysLong.se,
                                weekdaysShort: weekdaysShort.se,
                                firstDayOfWeek: 1,
                            }}
                        />
                    </div>
                </div>
            )}

            {settings.mod_event_filter_age_group && (
                <div className="grid-md-12 grid-lg-auto u-mb-2 u-mb-2@md u-mb-0@lg u-mb-0@xl">
                    <label htmlFor="filter-age" className="text-sm sr-only">
                        <strong>{translation.age}</strong>
                    </label>

                    <div className="input-group">
                        <span className="input-group-addon">{translation.age}:</span>
                        <input
                            type="number"
                            min="1"
                            id="filter-age"
                            className="form-control"
                            onChange={updateAge}
                            placeholder={translation.age}
                        />
                    </div>
                </div>
            )}

            {settings.mod_event_filter_categories && (
                <div className="grid-fit-content u-mr-auto u-mb-2 u-mb-2@md u-mb-0@lg u-mb-0@xl">
                    <label htmlFor="filter-categories" className="text-sm sr-only">
                        {translation.categories}
                    </label>

                    <Dropdown title={translation.categories} toggleClass="btn">
                        {categories.map(item => (
                            <label key={item.id} className="checkbox u-px-1">
                                <input
                                    id="filter-categories"
                                    type="checkbox"
                                    name="filter[categories][]"
                                    value={item.id}
                                    onChange={e => onCategoryChange(e, item.id)}
                                    checked={item.checked}
                                />{' '}
                                {item.title}
                            </label>
                        ))}
                    </Dropdown>
                </div>
            )}

            <div className="grid-fit-content">
                <Button title={translation.search} color="primary" submit />
            </div>
        </div>
    </form>
);

export default Filters;
