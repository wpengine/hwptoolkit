import React from 'react';

const EventPage = ({ params }) => {
    const { slug } = params;

    // Dummy event data
    const eventData = {
        title: "Sample Event",
        date: "2023-10-01",
        location: "New York, NY",
        description: "This is a sample event description. Join us for an exciting event filled with fun and learning."
    };

    return (
        <div>
            <h1>{eventData.title}</h1>
            <p>Date: {eventData.date}</p>
            <p>Location: {eventData.location}</p>
            <p>{eventData.description}</p>
        </div>
    );
};

export default EventPage;
