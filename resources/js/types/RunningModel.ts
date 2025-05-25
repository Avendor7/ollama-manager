export interface RunningModel {
    name: string;
    model: string;
    size: number;
    digest: string;
    details: {
        format: string;
        family: string;
        parameter_size: string;
        quantization_level: string;
        families: string[];
        parent_model: string;
    };
    expires_at: string;
    size_vram: number;
}

export interface RunningData {
    models: RunningModel[];
}
