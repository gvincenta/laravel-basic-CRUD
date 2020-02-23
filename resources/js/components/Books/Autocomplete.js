import React from 'react';
import { TextField, CircularProgress } from '@material-ui/core';
import { Autocomplete } from '@material-ui/lab';

/**asynchronous autocompletion text input adapted from:
 * https://material-ui.com/components/autocomplete/
 * @param props.data the data to be shown in the autocomplete box.
 * @param props.loading indicates whether we should show loading UI or not.
 * @param props.onChange what to do when a selection is made.
 * @returns the autocompletion box.
 */
export default function(props) {
    const { data, loading, onChange } = props;
    return (
        <Autocomplete
            id="existingAuthors"
            options={data}
            getOptionLabel={option => {
                return (
                    option.authorID +
                    ' ' +
                    option.firstName +
                    ' ' +
                    option.lastName
                );
            }}
            style={{ width: 300 }}
            loading={loading}
            renderInput={params => (
                <TextField
                    {...params}
                    label="Authors"
                    fullWidth
                    variant="outlined"
                    InputProps={{
                        ...params.InputProps,
                        endAdornment: (
                            <React.Fragment>
                                {loading ? (
                                    <CircularProgress
                                        color="inherit"
                                        size={20}
                                    />
                                ) : null}
                                {params.InputProps.endAdornment}
                            </React.Fragment>
                        )
                    }}
                />
            )}
            onChange={event => onChange(extractAuthor(event.target.innerHTML))}
        />
    );
}
function extractAuthor(authorString) {
    console.log(authorString, 'authorString');
    console.log(typeof authorString, 'authorString');

    var author = authorString.split(' ');
    return { authorID: author[0], firstName: author[1], lastName: author[2] };
}
